<?php

namespace App\Filament\Resources\EmployeeEnrollments\Pages;

use App\Filament\Resources\EmployeeEnrollments\EmployeeEnrollmentResource;
use App\Models\academic_years;
use App\Models\Employee;
use App\Models\EmployeeEnrollment;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

/**
 * قيد عدة موظفين في سنة دراسية دفعة واحدة. الوظيفة تؤخذ من بيانات الموظف.
 */
class CreateEmployeeEnrollment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EmployeeEnrollmentResource::class;

    public ?array $data = [];

    public static function canAccess(array $parameters = []): bool
    {
        return EmployeeEnrollmentResource::canCreate();
    }

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
            'employee_ids' => [],
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.create-employee-enrollment';
    }

    public function getTitle(): string
    {
        return 'قيد موظفين';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('السنة الدراسية')
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('السنة الدراسية')
                            ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->required()
                            ->live(),
                    ]),

                Section::make('اختيار الموظفين')
                    ->description('يُعرض فقط الموظفين غير المقيدين في السنة المختارة، والوظيفة تُسجل حسب بيانات كل موظف')
                    ->schema([
                        CheckboxList::make('employee_ids')
                            ->hiddenLabel()
                            ->options(fn (Get $get) => $this->availableEmployees($get('academic_year_id')))
                            ->columns(2)
                            ->gridDirection('row')
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->validationMessages(['required' => 'اختر موظفاً واحداً على الأقل'])
                            ->noSearchResultsMessage('لا يوجد موظفين'),
                    ])
                    ->visible(fn (Get $get) => filled($get('academic_year_id'))),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $created = DB::transaction(fn () => Employee::whereKey($data['employee_ids'])
            ->get()
            ->filter(fn (Employee $employee) => EmployeeEnrollment::enroll($employee, (int) $data['academic_year_id']))
            ->count());

        Notification::make()
            ->success()
            ->title('تم قيد ' . $created . ' موظف')
            ->send();

        $this->redirect(EmployeeEnrollmentResource::getUrl('index'));
    }

    private function availableEmployees(?string $academicYearId): array
    {
        if (! $academicYearId) {
            return [];
        }

        return Employee::query()
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->with(['employeeType', 'status'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Employee $e) => [
                $e->id => $e->name
                    . ' — ' . ($e->employeeType?->type_name ?? 'بدون وظيفة')
                    . ($e->status && $e->status->status_name !== 'مستمر' ? ' (' . $e->status->status_name . ')' : ''),
            ])
            ->all();
    }
}
