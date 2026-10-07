<?php

namespace App\Filament\Pages;

use App\Filament\Actions\IdCardActions;
use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\EmployeeType;
use App\Models\grade;
use App\Models\Section;
use App\Models\student;
use App\Models\StudentStatus;
use App\Services\IdCardPdfService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class IdCards extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'بطاقات التعريف';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المستخدمين والصلاحيات';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'إصدار بطاقات التعريف';

    public const SCOPE_SINGLE = 'single';
    public const SCOPE_SELECTED = 'selected';
    public const SCOPE_ALL = 'all';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return IdCardActions::canIssueStudentCards() || IdCardActions::canIssueEmployeeCards();
    }

    public function mount(): void
    {
        $this->form->fill([
            'card_type' => array_key_first($this->cardTypeOptions()),
            'scope' => self::SCOPE_SINGLE,
            'academic_year_id' => academic_years::getActiveId(),
            'student_status_id' => StudentStatus::where('name', 'نشط')->value('id'),
            'layout' => IdCardPdfService::LAYOUT_A4,
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.id-cards';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make('نوع البطاقة ونطاق الإصدار')
                    ->schema([
                        ToggleButtons::make('card_type')
                            ->label('نوع البطاقة')
                            ->options(fn () => $this->cardTypeOptions())
                            ->icons([
                                IdCardPdfService::TYPE_STUDENT => 'heroicon-o-academic-cap',
                                IdCardPdfService::TYPE_EMPLOYEE => 'heroicon-o-briefcase',
                            ])
                            ->inline()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('person_id', null);
                                $set('person_ids', []);
                            }),

                        ToggleButtons::make('scope')
                            ->label('إصدار البطاقات لـ')
                            ->options(fn (Get $get) => $this->isStudents($get)
                                ? [
                                    self::SCOPE_SINGLE => 'طالب واحد',
                                    self::SCOPE_SELECTED => 'مجموعة طلبة',
                                    self::SCOPE_ALL => 'كل الطلبة',
                                ]
                                : [
                                    self::SCOPE_SINGLE => 'موظف واحد',
                                    self::SCOPE_SELECTED => 'مجموعة موظفين',
                                    self::SCOPE_ALL => 'كل الموظفين',
                                ])
                            ->icons([
                                self::SCOPE_SINGLE => 'heroicon-o-user',
                                self::SCOPE_SELECTED => 'heroicon-o-users',
                                self::SCOPE_ALL => 'heroicon-o-user-group',
                            ])
                            ->inline()
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),

                SchemaSection::make('الاختيار')
                    ->schema([
                        Select::make('person_id')
                            ->label(fn (Get $get) => $this->isStudents($get) ? 'الطالب' : 'الموظف')
                            ->placeholder('ابحث بالاسم أو الرقم')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search, Get $get) => $this->searchPeople($search, $get))
                            ->getOptionLabelUsing(fn ($value, Get $get) => $this->personLabels([$value], $get)[$value] ?? null)
                            ->visible(fn (Get $get) => $get('scope') === self::SCOPE_SINGLE)
                            ->required(fn (Get $get) => $get('scope') === self::SCOPE_SINGLE),

                        Select::make('person_ids')
                            ->label(fn (Get $get) => $this->isStudents($get) ? 'الطلبة' : 'الموظفون')
                            ->placeholder('ابحث وأضف بالاسم أو الرقم')
                            ->multiple()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search, Get $get) => $this->searchPeople($search, $get))
                            ->getOptionLabelsUsing(fn (array $values, Get $get) => $this->personLabels($values, $get))
                            ->visible(fn (Get $get) => $get('scope') === self::SCOPE_SELECTED)
                            ->required(fn (Get $get) => $get('scope') === self::SCOPE_SELECTED),

                        // تصفية الطلبة
                        Select::make('grade_id')
                            ->label('الصف')
                            ->options(fn () => grade::orderBy('id')->pluck('name', 'id'))
                            ->placeholder('كل الصفوف')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('section_id', null))
                            ->visible(fn (Get $get) => $this->isStudents($get) && $get('scope') === self::SCOPE_ALL),

                        Select::make('section_id')
                            ->label('الشعبة')
                            // الربط بين الصف والشعبة موجود في جدول الفصول (classes)
                            ->options(fn (Get $get) => $get('grade_id')
                                ? Section::query()
                                    ->whereIn('id', ClassModel::where('grade_id', $get('grade_id'))->select('section_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                : [])
                            ->placeholder('كل الشعب')
                            ->disabled(fn (Get $get) => ! $get('grade_id'))
                            ->visible(fn (Get $get) => $this->isStudents($get) && $get('scope') === self::SCOPE_ALL),

                        Select::make('student_status_id')
                            ->label('حالة الطالب')
                            ->options(fn () => StudentStatus::pluck('name', 'id'))
                            ->placeholder('كل الحالات')
                            ->visible(fn (Get $get) => $this->isStudents($get) && $get('scope') === self::SCOPE_ALL),

                        // تصفية الموظفين
                        Select::make('emp_type_id')
                            ->label('نوع الموظف')
                            ->options(fn () => EmployeeType::pluck('type_name', 'id'))
                            ->placeholder('كل الأنواع')
                            ->visible(fn (Get $get) => ! $this->isStudents($get) && $get('scope') === self::SCOPE_ALL),

                        Select::make('employee_status_id')
                            ->label('حالة الموظف')
                            ->options(fn () => EmployeeStatus::pluck('status_name', 'id'))
                            ->placeholder('كل الحالات')
                            ->visible(fn (Get $get) => ! $this->isStudents($get) && $get('scope') === self::SCOPE_ALL),
                    ])
                    ->columns(fn (Get $get) => $get('scope') === self::SCOPE_ALL ? 3 : 1),

                SchemaSection::make('خيارات الطباعة')
                    ->schema([
                        IdCardActions::academicYearField()
                            ->visible(fn (Get $get) => $this->isStudents($get)),

                        IdCardActions::layoutField(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generate(): mixed
    {
        $data = $this->form->getState();

        try {
            return $this->isStudentType($data['card_type'] ?? null)
                ? IdCardActions::downloadStudents($this->resolveStudents($data), $data)
                : IdCardActions::downloadEmployees($this->resolveEmployees($data), $data);
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->danger()
                ->title('تعذر إصدار البطاقات')
                ->body($e->getMessage())
                ->send();

            return null;
        }
    }

    private function resolveStudents(array $data): Collection
    {
        $yearId = $data['academic_year_id'] ?? null;

        $query = match ($data['scope']) {
            self::SCOPE_SINGLE => student::whereKey($data['person_id']),
            self::SCOPE_SELECTED => student::whereKey($data['person_ids'] ?? []),
            // تصفية الصف/الشعبة تعتمد على قيد الطالب في السنة المختارة
            default => student::query()
                ->when(
                    ($data['grade_id'] ?? null) || ($data['section_id'] ?? null),
                    fn ($query) => $query->whereHas('enrollments', fn (Builder $q) => $q
                        ->where('academic_year_id', $yearId)
                        ->when($data['grade_id'] ?? null, fn ($q, $id) => $q->where('grade_id', $id))
                        ->when($data['section_id'] ?? null, fn ($q, $id) => $q->where('section_id', $id))),
                )
                ->when($data['student_status_id'] ?? null, fn ($q, $id) => $q->where('status_id', $id)),
        };

        // ترتيب الطباعة: الصف ثم الشعبة ثم الاسم
        return $query
            ->with(['enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)->with(['grade', 'section'])])
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->enrollments->first()?->grade_id ?? PHP_INT_MAX) <=> ($b->enrollments->first()?->grade_id ?? PHP_INT_MAX),
                fn ($a, $b) => strcmp((string) $a->enrollments->first()?->section?->name, (string) $b->enrollments->first()?->section?->name),
                fn ($a, $b) => strcmp((string) $a->full_name, (string) $b->full_name),
            ])
            ->values();
    }

    private function resolveEmployees(array $data): Collection
    {
        $query = match ($data['scope']) {
            self::SCOPE_SINGLE => Employee::whereKey($data['person_id']),
            self::SCOPE_SELECTED => Employee::whereKey($data['person_ids'] ?? []),
            default => Employee::query()
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->when($data['emp_type_id'] ?? null, fn ($q, $id) => $q->where('emp_type_id', $id))
                ->when($data['employee_status_id'] ?? null, fn ($q, $id) => $q->where('status_id', $id)),
        };

        return $query->with('employeeType')->orderBy('name')->get();
    }

    private function searchPeople(string $search, Get $get): array
    {
        if ($this->isStudents($get)) {
            return student::query()
                ->where(fn ($q) => $q
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('id', $search))
                ->orderBy('full_name')
                ->limit(50)
                ->get(['id', 'full_name'])
                ->mapWithKeys(fn ($s) => [$s->id => $s->full_name . ' (' . $s->id . ')'])
                ->all();
        }

        return Employee::query()
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($e) => [$e->id => $e->name . ' (' . $e->id . ')'])
            ->all();
    }

    private function personLabels(array $ids, Get $get): array
    {
        if ($this->isStudents($get)) {
            return student::whereKey($ids)
                ->get(['id', 'full_name'])
                ->mapWithKeys(fn ($s) => [$s->id => $s->full_name . ' (' . $s->id . ')'])
                ->all();
        }

        return Employee::whereKey($ids)
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($e) => [$e->id => $e->name . ' (' . $e->id . ')'])
            ->all();
    }

    private function cardTypeOptions(): array
    {
        return array_filter([
            IdCardPdfService::TYPE_STUDENT => IdCardActions::canIssueStudentCards() ? 'بطاقات الطلبة' : null,
            IdCardPdfService::TYPE_EMPLOYEE => IdCardActions::canIssueEmployeeCards() ? 'بطاقات الموظفين' : null,
        ]);
    }

    private function isStudents(Get $get): bool
    {
        return $this->isStudentType($get('card_type'));
    }

    private function isStudentType(?string $type): bool
    {
        return $type === IdCardPdfService::TYPE_STUDENT;
    }
}
