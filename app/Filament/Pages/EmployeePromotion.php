<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeEnrollment;
use App\Models\TeacherClass;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * ترحيل الموظفين من سنة دراسية إلى سنة جديدة، مع خيار نسخ توزيع المعلمين.
 */
class EmployeePromotion extends Page implements HasForms
{
    use InteractsWithForms;

    public const PERMISSION = 'employee-promotion.view';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationLabel = 'ترحيل الموظفين';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة الموظفين';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'ترحيل الموظفين للسنة الجديدة';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission(self::PERMISSION);
    }

    public function mount(): void
    {
        $fromYearId = academic_years::getActiveId();
        $toYearId = $fromYearId ? academic_years::where('id', '>', $fromYearId)->orderBy('id')->value('id') : null;

        $this->form->fill([
            'from_year_id' => $fromYearId,
            'to_year_id' => $toYearId,
            'employee_ids' => array_keys($this->candidates($fromYearId, $toYearId)),
            'copy_assignments' => true,
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.employee-promotion';
    }

    public function form(Schema $schema): Schema
    {
        $reselect = fn (Get $get, Set $set) => $set(
            'employee_ids',
            array_keys($this->candidates($get('from_year_id'), $get('to_year_id'))),
        );

        return $schema
            ->schema([
                Section::make('السنوات الدراسية')
                    ->schema([
                        Select::make('from_year_id')
                            ->label('من السنة الدراسية')
                            ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated($reselect),

                        Select::make('to_year_id')
                            ->label('إلى السنة الدراسية الجديدة')
                            ->options(fn (Get $get) => academic_years::where('id', '!=', $get('from_year_id'))
                                ->orderByDesc('id')
                                ->pluck('year_label', 'id'))
                            ->required()
                            ->different('from_year_id')
                            ->live()
                            ->afterStateUpdated($reselect)
                            ->helperText(fn (Get $get) => $get('to_year_id') && ! academic_years::whereKey($get('to_year_id'))->where('is_active', true)->exists()
                                ? 'بعد الترحيل لا تنسى تفعيل السنة الجديدة من صفحة السنوات الدراسية'
                                : null),
                    ])
                    ->columns(2),

                Section::make('الموظفين')
                    ->description('يُعرض الموظفين المقيدين في السنة الحالية وغير المقيدين في السنة الجديدة. شيل العلامة على اللي ما يبيش يترحل')
                    ->schema([
                        CheckboxList::make('employee_ids')
                            ->hiddenLabel()
                            ->options(fn (Get $get) => $this->candidates($get('from_year_id'), $get('to_year_id')))
                            ->columns(2)
                            ->gridDirection('row')
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->validationMessages(['required' => 'اختر موظفاً واحداً على الأقل'])
                            ->noSearchResultsMessage('لا يوجد موظفين'),

                        Toggle::make('copy_assignments')
                            ->label('نسخ توزيع المعلمين (المواد والفصول) للسنة الجديدة')
                            ->helperText(fn (Get $get) => 'سيتم نسخ ' . $this->assignmentsCount($get('from_year_id'), $get('employee_ids') ?? []) . ' توزيع للمعلمين المحددين، ويمكن تعديلها بعدين من شاشة توزيع المعلمين'),
                    ])
                    ->visible(fn (Get $get) => filled($get('from_year_id')) && filled($get('to_year_id'))),
            ])
            ->statePath('data');
    }

    public function promote(): void
    {
        $data = $this->form->getState();

        $fromYearId = (int) $data['from_year_id'];
        $toYearId = (int) $data['to_year_id'];
        $employeeIds = $data['employee_ids'];

        try {
            [$enrolled, $copied] = DB::transaction(function () use ($fromYearId, $toYearId, $employeeIds, $data) {
                $fromJobs = EmployeeEnrollment::where('academic_year_id', $fromYearId)
                    ->whereIn('employee_id', $employeeIds)
                    ->pluck('emp_type_id', 'employee_id');

                $enrolled = Employee::whereKey($employeeIds)->get()
                    ->filter(fn (Employee $e) => EmployeeEnrollment::enroll($e, $toYearId, $fromJobs[$e->id] ?? null))
                    ->count();

                $copied = 0;
                if ($data['copy_assignments'] ?? false) {
                    TeacherClass::where('academic_year_id', $fromYearId)
                        ->whereIn('teacher_id', $employeeIds)
                        ->get()
                        ->each(function (TeacherClass $tc) use ($toYearId, &$copied) {
                            $copied += DB::table('teacher_classes')->insertOrIgnore([
                                'teacher_id' => $tc->teacher_id,
                                'subject_id' => $tc->subject_id,
                                'class_id' => $tc->class_id,
                                'section_id' => $tc->section_id,
                                'academic_year_id' => $toYearId,
                            ]);
                        });
                }

                return [$enrolled, $copied];
            });
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->danger()->title('حدث خطأ أثناء الترحيل')->body($e->getMessage())->send();

            return;
        }

        $toYear = academic_years::find($toYearId)?->year_label;

        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'promoted',
                'description' => "ترحيل {$enrolled} موظف إلى السنة {$toYear}" . ($copied ? " ونسخ {$copied} توزيع" : ''),
                'model_type' => EmployeeEnrollment::class,
                'model_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // تجاهل أخطاء السجل
        }

        Notification::make()
            ->success()
            ->title('تم الترحيل')
            ->body("تم ترحيل {$enrolled} موظف إلى {$toYear}" . (($data['copy_assignments'] ?? false) ? " ونسخ {$copied} توزيع للمعلمين" : '') . '.')
            ->send();

        $this->form->fill([
            ...$data,
            'employee_ids' => array_keys($this->candidates($fromYearId, $toYearId)),
        ]);
    }

    /**
     * الموظفين المقيدين في السنة الحالية وغير المقيدين في السنة الجديدة.
     */
    private function candidates(?string $fromYearId, ?string $toYearId): array
    {
        if (! $fromYearId || ! $toYearId || $fromYearId === $toYearId) {
            return [];
        }

        return EmployeeEnrollment::query()
            ->where('academic_year_id', $fromYearId)
            ->whereNotIn('employee_id', EmployeeEnrollment::where('academic_year_id', $toYearId)->select('employee_id'))
            ->with(['employee.status', 'employeeType'])
            ->get()
            ->filter(fn ($en) => $en->employee)
            ->sortBy(fn ($en) => $en->employee->name)
            ->mapWithKeys(fn ($en) => [
                $en->employee_id => $en->employee->name
                    . ' — ' . ($en->employeeType?->type_name ?? 'بدون وظيفة')
                    . ($en->employee->status && $en->employee->status->status_name !== 'مستمر' ? ' (' . $en->employee->status->status_name . ')' : ''),
            ])
            ->all();
    }

    private function assignmentsCount(?string $fromYearId, array $employeeIds): int
    {
        if (! $fromYearId || ! $employeeIds) {
            return 0;
        }

        return TeacherClass::where('academic_year_id', $fromYearId)->whereIn('teacher_id', $employeeIds)->count();
    }
}
