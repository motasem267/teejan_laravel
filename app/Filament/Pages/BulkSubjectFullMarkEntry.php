<?php

namespace App\Filament\Pages;

use App\Models\AcademicPeriod;
use App\Models\grade;
use App\Models\GradeSubject;
use App\Models\mark;
use App\Models\subject;
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
use Illuminate\Support\Facades\DB;
use UnitEnum;

class BulkSubjectFullMarkEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedAcademicCap;
    protected static ?string $navigationLabel = 'الدرجة الكبرى للمواد (دفعة واحدة)';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون الأكاديمية';
    protected static ?int $navigationSort = -1;

    public ?array $data = [];

    public ?int $selectedGradeId = null;
    public ?int $selectedPeriodId = null;

    public array $subjects = [];
    public array $fullMarks = [];
    public array $existingSubjectIds = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('اختيار الصف والفترة')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('gradeID')
                                    ->label('الصف الدراسي')
                                    ->options(grade::orderBy('name')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state): void {
                                        $this->selectedGradeId = $state;
                                        $this->loadSubjects();
                                    }),

                                Select::make('academicperiodID')
                                    ->label('الفترة الدراسية')
                                    ->options(AcademicPeriod::orderBy('AcademicPeriodID')->pluck('PeriodName', 'AcademicPeriodID'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state): void {
                                        $this->selectedPeriodId = $state;
                                        $this->loadSubjects();
                                    }),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function loadSubjects(): void
    {
        if (!$this->selectedGradeId || !$this->selectedPeriodId) {
            $this->subjects = [];
            $this->fullMarks = [];
            $this->existingSubjectIds = [];
            return;
        }

        $subjectIds = GradeSubject::where('gradeID', $this->selectedGradeId)->pluck('subjectID');

        $this->subjects = subject::whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $existing = SubjectFullMark::where('gradeID', $this->selectedGradeId)
            ->where('academicperiodID', $this->selectedPeriodId)
            ->whereIn('subjectId', $subjectIds)
            ->pluck('FullMark', 'subjectId');

        $this->existingSubjectIds = $existing->keys()->toArray();

        $this->fullMarks = [];
        foreach ($this->subjects as $subj) {
            $this->fullMarks[$subj['id']] = $existing[$subj['id']] ?? 100;
        }
    }

    public function save(): void
    {
        if (empty($this->subjects)) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار الصف والفترة أولاً')
                ->danger()
                ->send();
            return;
        }

        $invalid = [];
        foreach ($this->subjects as $subj) {
            $value = $this->fullMarks[$subj['id']] ?? null;
            if ($value === null || $value === '' || !is_numeric($value) || $value < 1 || $value > 1000) {
                $invalid[] = $subj['name'];
            }
        }

        if (!empty($invalid)) {
            Notification::make()
                ->title('قيم غير صالحة')
                ->body('الدرجة الكبرى يجب أن تكون بين 1 و1000 للمواد التالية: ' . implode('، ', $invalid))
                ->danger()
                ->send();
            return;
        }

        // منع خفض الدرجة الكبرى لمادة تحت درجات طلاب محفوظة مسبقاً
        $blocked = [];
        foreach ($this->subjects as $subj) {
            $newValue = (float) $this->fullMarks[$subj['id']];

            $maxExistingMark = mark::where('subject_id', $subj['id'])
                ->where('AcademicPeriodID', $this->selectedPeriodId)
                ->whereHas('enrollment', fn ($q) => $q->where('grade_id', $this->selectedGradeId))
                ->max('student_mark');

            if ($maxExistingMark !== null && $newValue < $maxExistingMark) {
                $blocked[] = $subj['name'] . ' (أعلى درجة طالب محفوظة: ' . $maxExistingMark . ')';
            }
        }

        if (!empty($blocked)) {
            Notification::make()
                ->title('لا يمكن خفض الدرجة الكبرى')
                ->body('توجد درجات طلاب أعلى من الحد الجديد للمواد التالية، عدّل درجات الطلاب أولاً: ' . implode('، ', $blocked))
                ->danger()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            $cascadedCount = 0;

            foreach ($this->subjects as $subj) {
                $subjectId = $subj['id'];
                $newValue = (float) $this->fullMarks[$subjectId];

                $existing = SubjectFullMark::where('subjectId', $subjectId)
                    ->where('academicperiodID', $this->selectedPeriodId)
                    ->where('gradeID', $this->selectedGradeId)
                    ->first();

                $changed = !$existing || (float) $existing->FullMark !== $newValue;

                SubjectFullMark::updateOrCreate(
                    [
                        'subjectId' => $subjectId,
                        'academicperiodID' => $this->selectedPeriodId,
                        'gradeID' => $this->selectedGradeId,
                    ],
                    ['FullMark' => $newValue],
                );

                if ($changed) {
                    $cascadedCount += mark::where('subject_id', $subjectId)
                        ->where('AcademicPeriodID', $this->selectedPeriodId)
                        ->whereHas('enrollment', fn ($q) => $q->where('grade_id', $this->selectedGradeId))
                        ->update(['full_mark' => $newValue]);
                }
            }

            DB::commit();

            $body = 'تم حفظ الدرجة الكبرى لـ ' . count($this->subjects) . ' مادة';
            if ($cascadedCount > 0) {
                $body .= '، وتم تحديث ' . $cascadedCount . ' درجة طالب محفوظة مسبقاً لتطابق القيمة الجديدة';
            }

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body($body)
                ->success()
                ->send();

            $this->loadSubjects();
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
        return 'filament.pages.bulk-subject-full-mark-entry';
    }

    public function getTitle(): string
    {
        return 'الدرجة الكبرى للمواد (دفعة واحدة)';
    }

    public static function getNavigationLabel(): string
    {
        return 'الدرجة الكبرى للمواد (دفعة واحدة)';
    }
}
