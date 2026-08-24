<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\AcademicPeriod;
use App\Models\ClassModel;
use App\Models\GradeSubject;
use App\Models\mark;
use App\Models\StudentEnrollment;
use App\Models\SubjectFullMark;
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

class BulkMarksEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedAcademicCap;
    protected static ?string $navigationLabel = 'إدخال الدرجات (دفعة واحدة)';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('marks.view') || $user->hasPermission('marks.create');
    }

    public ?array $data = [];

    public ?int $selectedClassId = null;
    public ?int $selectedSubjectId = null;
    public ?int $selectedPeriodId = null;

    public array $students = [];
    public ?int $fullMark = null;
    public array $studentMarks = [];

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
                Section::make('اختيار الفصل والمادة')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('academic_year_id')
                                    ->label('السنة الدراسية')
                                    ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                                    ->default(academic_years::getActiveId())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state): void {
                                        $this->data['class_id'] = null;
                                        $this->data['subject_id'] = null;
                                        $this->resetRoster();
                                    }),

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
                                        $this->data['subject_id'] = null;
                                        $this->resetRoster();
                                    }),

                                Select::make('subject_id')
                                    ->label('المادة')
                                    ->options(function (): array {
                                        if (!$this->selectedClassId) {
                                            return [];
                                        }

                                        $class = ClassModel::find($this->selectedClassId);
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
                                    ->reactive()
                                    ->disabled(fn (): bool => !$this->selectedClassId)
                                    ->afterStateUpdated(function ($state): void {
                                        $this->selectedSubjectId = $state;
                                        $this->loadRoster();
                                    }),

                                Select::make('AcademicPeriodID')
                                    ->label('الفترة الدراسية')
                                    ->options(AcademicPeriod::orderBy('AcademicPeriodID')->pluck('PeriodName', 'AcademicPeriodID'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state): void {
                                        $this->selectedPeriodId = $state;
                                        $this->loadRoster();
                                    }),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function resetRoster(): void
    {
        $this->students = [];
        $this->fullMark = null;
        $this->studentMarks = [];
    }

    protected function loadRoster(): void
    {
        if (!$this->selectedClassId || !$this->selectedSubjectId || !$this->selectedPeriodId) {
            $this->resetRoster();
            return;
        }

        $class = ClassModel::find($this->selectedClassId);
        if (!$class) {
            $this->resetRoster();
            return;
        }

        $yearId = $this->data['academic_year_id'] ?? academic_years::getActiveId();

        $enrollments = StudentEnrollment::with('student')
            ->where('grade_id', $class->grade_id)
            ->where('section_id', $class->section_id)
            ->where('academic_year_id', $yearId)
            ->get()
            ->filter(fn (StudentEnrollment $e) => $e->student !== null)
            ->sortBy(fn (StudentEnrollment $e) => $e->student->full_name)
            ->values();

        $this->students = $enrollments->map(fn (StudentEnrollment $e) => [
            'enrollment_id' => $e->id,
            'full_name' => $e->student->full_name,
        ])->toArray();

        $this->fullMark = SubjectFullMark::where('subjectId', $this->selectedSubjectId)
            ->where('academicperiodID', $this->selectedPeriodId)
            ->where('gradeID', $class->grade_id)
            ->value('FullMark');

        $enrollmentIds = $enrollments->pluck('id');

        $this->studentMarks = mark::where('subject_id', $this->selectedSubjectId)
            ->where('AcademicPeriodID', $this->selectedPeriodId)
            ->whereIn('student_inrollment_id', $enrollmentIds)
            ->pluck('student_mark', 'student_inrollment_id')
            ->toArray();
    }

    public function save(): void
    {
        if (empty($this->students)) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار الفصل والمادة والفترة أولاً')
                ->danger()
                ->send();
            return;
        }

        if (!$this->fullMark) {
            Notification::make()
                ->title('لا توجد درجة كبرى')
                ->body('لم يتم تحديد الدرجة الكبرى لهذه المادة في هذا الصف والفترة. حدّدها أولاً من شاشة "الدرجة الكبرى للمواد".')
                ->danger()
                ->send();
            return;
        }

        $invalid = [];
        $toSave = [];

        foreach ($this->students as $student) {
            $enrollmentId = $student['enrollment_id'];
            $value = $this->studentMarks[$enrollmentId] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (!is_numeric($value) || $value < 0 || $value > $this->fullMark) {
                $invalid[] = $student['full_name'];
                continue;
            }

            $toSave[$enrollmentId] = $value;
        }

        if (!empty($invalid)) {
            Notification::make()
                ->title('درجات غير صالحة')
                ->body('الدرجة يجب أن تكون بين 0 و' . $this->fullMark . ' للطلاب التالين: ' . implode('، ', $invalid))
                ->danger()
                ->send();
            return;
        }

        if (empty($toSave)) {
            Notification::make()
                ->title('لا يوجد شيء لحفظه')
                ->body('يرجى إدخال درجة طالب واحد على الأقل')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($toSave as $enrollmentId => $value) {
                mark::updateOrCreate(
                    [
                        'student_inrollment_id' => $enrollmentId,
                        'subject_id' => $this->selectedSubjectId,
                        'AcademicPeriodID' => $this->selectedPeriodId,
                    ],
                    [
                        'full_mark' => $this->fullMark,
                        'student_mark' => $value,
                    ],
                );
            }

            DB::commit();

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم حفظ درجات ' . count($toSave) . ' طالب/طالبة')
                ->success()
                ->send();

            $this->loadRoster();
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
        return 'filament.pages.bulk-marks-entry';
    }

    public function getTitle(): string
    {
        return 'إدخال الدرجات (دفعة واحدة)';
    }

    public static function getNavigationLabel(): string
    {
        return 'إدخال الدرجات (دفعة واحدة)';
    }
}
