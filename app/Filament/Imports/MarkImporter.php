<?php

namespace App\Filament\Imports;

use App\Models\mark;
use App\Models\student;
use App\Models\subject;
use App\Models\AcademicPeriod;
use App\Models\academic_years;
use App\Models\GradeSubject;
use App\Models\StudentEnrollment;
use App\Models\SubjectFullMark;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MarkImporter extends Importer
{
    protected static ?string $model = mark::class;

    protected static array $studentsCache       = [];
    protected static array $subjectsCache       = [];
    protected static array $periodsCache        = [];
    protected static array $yearsCache          = [];
    protected static array $enrollmentsCache    = [];
    protected static array $gradeSubjectsCache  = [];
    protected static array $fullMarksCache      = [];
    protected static array $existingMarksCache  = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('student_national_id')
                ->label('الرقم الوطني للطالب')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null)   // نتولاها في resolveRecord
                ->rules([
                    'required',
                    function (string $attribute, $value, \Closure $fail) {
                        if (!static::findStudentByNationalId($value)) {
                            $fail("لا يوجد طالب بالرقم الوطني: {$value}");
                        }
                    },
                ]),

            ImportColumn::make('academic_year_id')
                ->label('رقم السنة الدراسية')
                ->requiredMapping()
                ->numeric()
                ->fillRecordUsing(fn () => null)   // لا يوجد هذا العمود في marks
                ->rules([
                    'required', 'integer',
                    function (string $attribute, $value, \Closure $fail) {
                        if (!static::findAcademicYear((int) $value)) {
                            $fail("السنة الدراسية رقم {$value} غير موجودة.");
                        }
                    },
                ]),

            ImportColumn::make('subject_id')
                ->label('رقم المادة')
                ->requiredMapping()
                ->numeric()
                ->rules([
                    'required', 'integer',
                    function (string $attribute, $value, \Closure $fail) {
                        if (!static::findSubject((int) $value)) {
                            $fail("المادة رقم {$value} غير موجودة.");
                        }
                    },
                ]),

            ImportColumn::make('AcademicPeriodID')
                ->label('رقم الفترة الدراسية')
                ->requiredMapping()
                ->numeric()
                ->rules([
                    'required', 'integer',
                    function (string $attribute, $value, \Closure $fail) {
                        if (!static::findAcademicPeriod((int) $value)) {
                            $fail("الفترة الدراسية رقم {$value} غير موجودة.");
                        }
                    },
                ]),

            ImportColumn::make('student_mark')
                ->label('الدرجة')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?mark
    {
        static::loadCaches();

        $nationalId  = $this->data['student_national_id'];
        $yearId      = (int) $this->data['academic_year_id'];
        $subjectId   = (int) $this->data['subject_id'];
        $periodId    = (int) $this->data['AcademicPeriodID'];
        $studentMark = (float) $this->data['student_mark'];

        $studentArr = static::findStudentByNationalId($nationalId);
        if (!$studentArr) {
            throw new \Exception("لا يوجد طالب بالرقم الوطني: {$nationalId}");
        }
        $studentId   = $studentArr['id'];
        $studentName = $studentArr['full_name'] ?? $nationalId;

        $enrollmentKey = $studentId . '_' . $yearId;
        $enrollment    = static::$enrollmentsCache[$enrollmentKey] ?? null;
        if (!$enrollment) {
            throw new \Exception("الطالب [{$studentName}] ليس لديه قيد في السنة الدراسية رقم {$yearId}.");
        }
        $enrollmentId = $enrollment->id;
        $gradeId      = $enrollment->grade_id;

        $gradeSubjectKey = $gradeId . '_' . $subjectId;
        if (!isset(static::$gradeSubjectsCache[$gradeSubjectKey])) {
            throw new \Exception("المادة رقم {$subjectId} غير مرتبطة بصف الطالب [{$studentName}].");
        }

        $markKey = $enrollmentId . '_' . $subjectId . '_' . $periodId;
        if (isset(static::$existingMarksCache[$markKey])) {
            throw new \Exception("الطالب [{$studentName}] لديه درجة مكررة في الملف لهذه المادة والفترة.");
        }
        if (mark::markExists($enrollmentId, $subjectId, $periodId)) {
            throw new \Exception("الطالب [{$studentName}] لديه درجة مسجَّلة مسبقاً لهذه المادة والفترة.");
        }

        $fullMarkKey = $subjectId . '_' . $periodId;
        $fullMark    = static::$fullMarksCache[$fullMarkKey] ?? null;
        if ($fullMark !== null && $studentMark > $fullMark) {
            throw new \Exception("الدرجة {$studentMark} أكبر من الدرجة الكبرى {$fullMark}.");
        }

        static::$existingMarksCache[$markKey] = true;

        return new mark([
            'student_inrollment_id' => $enrollmentId,
            'subject_id'            => $subjectId,
            'AcademicPeriodID'      => $periodId,
            'student_mark'          => $studentMark,
            'full_mark'             => $fullMark,
        ]);
    }

    protected static function loadCaches(): void
    {
        if (empty(static::$studentsCache)) {
            static::$studentsCache = student::all()
                ->keyBy('national_id')
                ->map(fn ($s) => $s->toArray())
                ->toArray();
        }
        if (empty(static::$subjectsCache)) {
            static::$subjectsCache = subject::all()->keyBy('id')->toArray();
        }
        if (empty(static::$periodsCache)) {
            static::$periodsCache = AcademicPeriod::all()->keyBy('AcademicPeriodID')->toArray();
        }
        if (empty(static::$yearsCache)) {
            static::$yearsCache = academic_years::all()->keyBy('id')->toArray();
        }
        if (empty(static::$enrollmentsCache)) {
            StudentEnrollment::all()->each(function ($e) {
                $key = $e->student_id . '_' . $e->academic_year_id;
                static::$enrollmentsCache[$key] = $e;
            });
        }
        if (empty(static::$gradeSubjectsCache)) {
            GradeSubject::all()->each(function ($gs) {
                $key = $gs->gradeID . '_' . $gs->subjectID;
                static::$gradeSubjectsCache[$key] = true;
            });
        }
        if (empty(static::$fullMarksCache)) {
            SubjectFullMark::all()->each(function ($fm) {
                $key = $fm->subjectId . '_' . $fm->academicperiodID;
                static::$fullMarksCache[$key] = $fm->FullMark;
            });
        }
    }

    protected static function findStudentByNationalId($nationalId): ?array
    {
        static::loadCaches();
        return static::$studentsCache[$nationalId] ?? null;
    }

    protected static function findSubject(int $id): mixed
    {
        static::loadCaches();
        return static::$subjectsCache[$id] ?? null;
    }

    protected static function findAcademicPeriod(int $id): mixed
    {
        static::loadCaches();
        return static::$periodsCache[$id] ?? null;
    }

    protected static function findAcademicYear(int $id): mixed
    {
        static::loadCaches();
        return static::$yearsCache[$id] ?? null;
    }

    /**
     * تشغيل الاستيراد بشكل فوري (sync) دون الحاجة لـ queue worker.
     */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد ' . number_format($import->successful_rows) . ' درجة بنجاح.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' فشل استيراد ' . number_format($failedRowsCount) . ' درجة.';
        }
        return $body;
    }

    public static function clearCaches(): void
    {
        static::$studentsCache      = [];
        static::$subjectsCache      = [];
        static::$periodsCache       = [];
        static::$yearsCache         = [];
        static::$enrollmentsCache   = [];
        static::$gradeSubjectsCache = [];
        static::$fullMarksCache     = [];
        static::$existingMarksCache = [];
    }
}