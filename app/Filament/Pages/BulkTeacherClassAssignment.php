<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\Employee;
use App\Models\GradeSubject;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class BulkTeacherClassAssignment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'تخصيص معلم لعدة فصول (دفعة واحدة)';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون الأكاديمية';
    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('teacher-classes.view') || $user->hasPermission('teacher-classes.create');
    }

    public ?array $data = [];

    public ?int $pendingDeleteCount = null;

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
            'assignments' => [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('اختيار المعلم')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('teacher_id')
                                    ->label('المعلم')
                                    ->options(fn (): array => Employee::whereHas(
                                        'employeeType',
                                        fn (Builder $q) => $q->where('type_name', 'LIKE', '%معلم%'),
                                    )->pluck('name', 'id')->toArray())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set) => $this->loadAssignments($set)),

                                Select::make('academic_year_id')
                                    ->label('السنة الدراسية')
                                    ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                                    ->default(academic_years::getActiveId())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set) => $this->loadAssignments($set)),
                            ]),
                    ]),

                Section::make('التخصيصات')
                    ->description('أضف كل مادة/فصل يدرّسه هذا المعلم، ثم احفظ الكل مرة واحدة')
                    ->schema([
                        Repeater::make('assignments')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('class_id')
                                    ->label('الفصل (الصف والشعبة)')
                                    ->options(fn (): array => ClassModel::with(['grade', 'section'])
                                        ->get()
                                        ->mapWithKeys(fn (ClassModel $class) => [
                                            $class->id => ($class->grade->name ?? '-') . ' - ' . ($class->section->name ?? '-'),
                                        ])
                                        ->toArray())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('subject_id', null)),

                                Select::make('subject_id')
                                    ->label('المادة')
                                    ->options(function (Get $get): array {
                                        $classId = $get('class_id');
                                        if (!$classId) {
                                            return [];
                                        }

                                        $class = ClassModel::find($classId);
                                        if (!$class) {
                                            return [];
                                        }

                                        return GradeSubject::with('subject')
                                            ->where('gradeID', $class->grade_id)
                                            ->get()
                                            ->mapWithKeys(fn (GradeSubject $gs) => [
                                                $gs->subjectID => $gs->subject?->name ?? '-',
                                            ])
                                            ->filter()
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabled(fn (Get $get) => !$get('class_id')),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة تخصيص')
                            ->reorderable(false)
                            ->defaultItems(0),
                    ])
                    ->visible(fn (Get $get) => (bool) $get('teacher_id')),
            ])
            ->statePath('data');
    }

    protected function loadAssignments(?Set $set = null): void
    {
        $this->pendingDeleteCount = null;

        $teacherId = $this->data['teacher_id'] ?? null;
        $yearId = $this->data['academic_year_id'] ?? null;

        $rows = (!$teacherId || !$yearId)
            ? []
            : TeacherClass::where('teacher_id', $teacherId)
                ->where('academic_year_id', $yearId)
                ->get()
                ->map(fn (TeacherClass $tc) => [
                    'class_id' => $tc->class_id,
                    'subject_id' => $tc->subject_id,
                ])
                ->values()
                ->toArray();

        if ($set) {
            $set('assignments', $rows);
        } else {
            $this->data['assignments'] = $rows;
        }
    }

    public function save(bool $confirmDeletion = false): void
    {
        $teacherId = $this->data['teacher_id'] ?? null;
        $yearId = $this->data['academic_year_id'] ?? null;
        $rows = $this->data['assignments'] ?? [];

        if (!$teacherId || !$yearId) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار المعلم والسنة الدراسية أولاً')
                ->danger()
                ->send();
            return;
        }

        $seen = [];
        foreach ($rows as $row) {
            if (empty($row['class_id']) || empty($row['subject_id'])) {
                Notification::make()
                    ->title('بيانات ناقصة')
                    ->body('يرجى اختيار الفصل والمادة في كل سطر')
                    ->danger()
                    ->send();
                return;
            }

            $key = $row['class_id'] . '_' . $row['subject_id'];
            if (isset($seen[$key])) {
                Notification::make()
                    ->title('تكرار')
                    ->body('لا يمكن تكرار نفس الفصل والمادة أكثر من مرة')
                    ->danger()
                    ->send();
                return;
            }
            $seen[$key] = true;
        }

        $existing = TeacherClass::where('teacher_id', $teacherId)
            ->where('academic_year_id', $yearId)
            ->get()
            ->keyBy(fn (TeacherClass $tc) => $tc->class_id . '_' . $tc->subject_id);

        $submittedKeys = array_keys($seen);

        $toDelete = $existing->filter(fn (TeacherClass $tc, string $key) => !in_array($key, $submittedKeys, true));
        $toCreate = array_filter($rows, fn (array $row) => !$existing->has($row['class_id'] . '_' . $row['subject_id']));

        if ($toDelete->isNotEmpty() && !$confirmDeletion) {
            $scheduleCount = SchoolSchedule::whereIn('teacher_class_id', $toDelete->pluck('id'))->count();

            if ($scheduleCount > 0) {
                $this->pendingDeleteCount = $scheduleCount;

                Notification::make()
                    ->title('تحذير: سيتم حذف حصص من الجدول الأسبوعي')
                    ->body('إزالة ' . $toDelete->count() . ' تخصيص/تخصيصات سيحذف تلقائياً ' . $scheduleCount . ' حصة من الجدول الأسبوعي المرتبطة بها. اضغط "تأكيد الحذف والحفظ" للمتابعة.')
                    ->warning()
                    ->persistent()
                    ->send();
                return;
            }
        }

        DB::beginTransaction();

        try {
            foreach ($toDelete as $tc) {
                $tc->delete();
            }

            foreach ($toCreate as $row) {
                $class = ClassModel::find($row['class_id']);

                TeacherClass::create([
                    'teacher_id' => $teacherId,
                    'subject_id' => $row['subject_id'],
                    'class_id' => $row['class_id'],
                    'section_id' => $class?->section_id,
                    'academic_year_id' => $yearId,
                ]);
            }

            DB::commit();

            $this->pendingDeleteCount = null;

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم حفظ تخصيصات المعلم (' . count($rows) . ' تخصيص)')
                ->success()
                ->send();

            $this->loadAssignments();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function confirmAndSave(): void
    {
        $this->save(confirmDeletion: true);
    }

    public function getView(): string
    {
        return 'filament.pages.bulk-teacher-class-assignment';
    }

    public function getTitle(): string
    {
        return 'تخصيص معلم لعدة فصول (دفعة واحدة)';
    }

    public static function getNavigationLabel(): string
    {
        return 'تخصيص معلم لعدة فصول (دفعة واحدة)';
    }
}
