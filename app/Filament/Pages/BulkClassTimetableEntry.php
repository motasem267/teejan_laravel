<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\Day;
use App\Models\LessonTime;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class BulkClassTimetableEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'إدخال الجدول الأسبوعي (دفعة واحدة)';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';
    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('school-schedules.view') || $user->hasPermission('school-schedules.create');
    }

    public ?array $data = [];

    public ?int $selectedClassId = null;

    public array $days = [];
    public array $lessonTimes = [];
    public array $teacherClassOptions = [];
    public array $scheduleGrid = [];
    public bool $hasNoAssignments = false;

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('اختيار الفصل')
                    ->schema([
                        Grid::make(2)
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
                                    ->afterStateUpdated(function ($state): void {
                                        $this->selectedClassId = $state;
                                        $this->loadGrid();
                                    }),

                                Select::make('academic_year_id')
                                    ->label('السنة الدراسية')
                                    ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                                    ->default(academic_years::getActiveId())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->loadGrid()),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function thisClassTeacherClassIds(): \Illuminate\Support\Collection
    {
        $yearId = $this->data['academic_year_id'] ?? academic_years::getActiveId();

        return TeacherClass::where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $yearId)
            ->pluck('id');
    }

    protected function loadGrid(): void
    {
        $this->days = [];
        $this->lessonTimes = [];
        $this->teacherClassOptions = [];
        $this->scheduleGrid = [];
        $this->hasNoAssignments = false;

        if (!$this->selectedClassId || empty($this->data['academic_year_id'])) {
            return;
        }

        $teacherClassIds = $this->thisClassTeacherClassIds();

        if ($teacherClassIds->isEmpty()) {
            $this->hasNoAssignments = true;
            return;
        }

        $this->teacherClassOptions = TeacherClass::whereIn('id', $teacherClassIds)
            ->with(['teacher', 'subject'])
            ->get()
            ->mapWithKeys(fn (TeacherClass $tc) => [
                $tc->id => ($tc->teacher->name ?? '-') . ' - ' . ($tc->subject->name ?? '-'),
            ])
            ->toArray();

        $this->days = Day::orderBy('day_order')->get(['id', 'day_name_ar'])->toArray();

        $this->lessonTimes = LessonTime::orderBy('start_time')
            ->get(['id', 'period_number', 'start_time', 'end_time', 'is_break'])
            ->toArray();

        $grid = [];
        foreach (SchoolSchedule::whereIn('teacher_class_id', $teacherClassIds)->get() as $schedule) {
            $grid[$schedule->day_id][$schedule->lesson_time_id] = $schedule->teacher_class_id;
        }
        $this->scheduleGrid = $grid;
    }

    public function save(): void
    {
        if (!$this->selectedClassId || empty($this->teacherClassOptions)) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار الفصل أولاً')
                ->danger()
                ->send();
            return;
        }

        $teacherClassIds = $this->thisClassTeacherClassIds();
        $validTcIds = array_keys($this->teacherClassOptions);

        // إعادة التحقق من كل قيمة مُرسلة مقابل القيم المسموح بها لهذا الفصل فقط
        foreach ($this->scheduleGrid as $dayId => $row) {
            foreach ($row as $lessonTimeId => $tcId) {
                if ($tcId !== null && $tcId !== '' && !in_array((int) $tcId, $validTcIds, true)) {
                    Notification::make()
                        ->title('خطأ')
                        ->body('قيمة غير صالحة في الجدول، أعد تحميل الصفحة وحاول مجدداً')
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        $teacherIdByTcId = TeacherClass::whereIn('id', $teacherClassIds)->pluck('teacher_id', 'id');

        // مرحلة 1: التحقق من تعارض حجز نفس المعلم في فصل آخر بنفس التوقيت
        $conflicts = [];
        foreach ($this->scheduleGrid as $dayId => $row) {
            foreach ($row as $lessonTimeId => $tcId) {
                if (!$tcId) {
                    continue;
                }

                $teacherId = $teacherIdByTcId[$tcId] ?? null;
                if (!$teacherId) {
                    continue;
                }

                $conflict = SchoolSchedule::where('day_id', $dayId)
                    ->where('lesson_time_id', $lessonTimeId)
                    ->whereNotIn('teacher_class_id', $teacherClassIds)
                    ->whereHas('teacherClass', fn ($q) => $q->where('teacher_id', $teacherId))
                    ->with(['teacherClass.teacher', 'teacherClass.classModel.grade', 'teacherClass.classModel.section', 'day', 'lessonTime'])
                    ->first();

                if ($conflict) {
                    $conflicts[] = sprintf(
                        'المعلم %s محجوز في %s - %s يوم %s الحصة %s',
                        $conflict->teacherClass->teacher->name ?? '-',
                        $conflict->teacherClass->classModel->grade->name ?? '-',
                        $conflict->teacherClass->classModel->section->name ?? '-',
                        $conflict->day->day_name_ar ?? '-',
                        $conflict->lessonTime->period_number ?? '-',
                    );
                }
            }
        }

        if (!empty($conflicts)) {
            Notification::make()
                ->title('تعارض في الجدول')
                ->body(implode(' | ', array_unique($conflicts)))
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // مرحلة 2: الكتابة الفعلية
        DB::beginTransaction();

        try {
            $editableLessonTimeIds = collect($this->lessonTimes)
                ->reject(fn (array $lt) => $lt['is_break'])
                ->pluck('id');

            foreach ($this->days as $day) {
                foreach ($editableLessonTimeIds as $lessonTimeId) {
                    $submittedTcId = $this->scheduleGrid[$day['id']][$lessonTimeId] ?? null;
                    $submittedTcId = ($submittedTcId === '' ? null : $submittedTcId);

                    $currentRow = SchoolSchedule::whereIn('teacher_class_id', $teacherClassIds)
                        ->where('day_id', $day['id'])
                        ->where('lesson_time_id', $lessonTimeId)
                        ->first();

                    if ($submittedTcId === null) {
                        $currentRow?->delete();
                        continue;
                    }

                    if ($currentRow && (int) $currentRow->teacher_class_id === (int) $submittedTcId) {
                        continue;
                    }

                    $currentRow?->delete();

                    SchoolSchedule::create([
                        'teacher_class_id' => $submittedTcId,
                        'day_id' => $day['id'],
                        'lesson_time_id' => $lessonTimeId,
                    ]);
                }
            }

            DB::commit();

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم حفظ الجدول الأسبوعي لهذا الفصل')
                ->success()
                ->send();

            $this->loadGrid();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getView(): string
    {
        return 'filament.pages.bulk-class-timetable-entry';
    }

    public function getTitle(): string
    {
        return 'إدخال الجدول الأسبوعي (دفعة واحدة)';
    }

    public static function getNavigationLabel(): string
    {
        return 'إدخال الجدول الأسبوعي (دفعة واحدة)';
    }
}
