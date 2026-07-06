<?php

namespace App\Http\Controllers;

use App\Models\student;
use App\Models\mark;
use App\Models\StudentEnrollment;
use App\Models\AcademicPeriodGrade;
use App\Models\SubjectFullMark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * بناء بيانات الدرجات لقيد محدد (enrollment) مع مراعاة صلاحية العرض.
     * منطق مشترك بين getStudentData و getStudentById.
     */
    private function buildEnrollmentMarksData(StudentEnrollment $enrollment): array
    {
        $gradeId = $enrollment->grade_id;

        // الفترات المتاحة للعرض (IsViewed = 1) لهذا الصف
        $viewablePeriods = AcademicPeriodGrade::where('GradeID', $gradeId)
            ->where('IsViewed', 1)
            ->with('academicPeriod')
            ->get()
            ->sortBy('AcademicPeriodID')
            ->values();

        $viewablePeriodIds = $viewablePeriods->pluck('AcademicPeriodID')->toArray();

        // درجات الطالب في هذا القيد المرتبطة بالفترات المتاحة فقط
        $marks = mark::with('subject')
            ->where('student_inrollment_id', $enrollment->id)
            ->whereIn('AcademicPeriodID', $viewablePeriodIds)
            ->get();

        // المواد الموجودة في الدرجات
        $allSubjects = $marks->pluck('subject')->unique('id')->sortBy('id')->values();

        // بناء جدول المواد × الفترات
        $tableData = [];
        foreach ($allSubjects as $subject) {
            $subjectRow = [
                'subject_id'   => $subject->id,
                'subject_name' => $subject->name,
                'periods'      => [],
            ];

            foreach ($viewablePeriods as $period) {
                $mark = $marks->first(fn ($m) =>
                    $m->subject_id == $subject->id &&
                    $m->AcademicPeriodID == $period->AcademicPeriodID
                );

                $fullMarkRecord = SubjectFullMark::where('academicperiodID', $period->AcademicPeriodID)
                    ->where('subjectId', $subject->id)
                    ->where('gradeID', $gradeId)
                    ->first();

                $subjectRow['periods'][] = [
                    'period_id'    => $period->AcademicPeriodID,
                    'period_name'  => $period->academicPeriod->PeriodName,
                    'full_mark'    => $fullMarkRecord?->FullMark,
                    'student_mark' => $mark?->student_mark,
                ];
            }

            $tableData[] = $subjectRow;
        }

        // إجمالي كل فترة
        $periodTotals = [];
        foreach ($viewablePeriods as $period) {
            $periodMarks    = $marks->where('AcademicPeriodID', $period->AcademicPeriodID);
            $totalStudent   = 0;
            $totalFull      = 0;

            foreach ($periodMarks as $m) {
                $fm = SubjectFullMark::where('academicperiodID', $period->AcademicPeriodID)
                    ->where('subjectId', $m->subject_id)
                    ->where('gradeID', $gradeId)
                    ->first();

                if ($fm) {
                    $totalFull    += $fm->FullMark;
                    $totalStudent += $m->student_mark;
                }
            }

            $periodTotals[] = [
                'period_id'           => $period->AcademicPeriodID,
                'period_name'         => $period->academicPeriod->PeriodName,
                'total_student_marks' => $totalStudent,
                'total_full_marks'    => $totalFull,
            ];
        }

        return [
            'enrollment_id' => $enrollment->id,
            'academic_year' => [
                'id'    => $enrollment->academic_year_id,
                'label' => $enrollment->academicYear->year_label ?? null,
            ],
            'grade' => [
                'id'   => $enrollment->grade->id,
                'name' => $enrollment->grade->name,
            ],
            'section' => $enrollment->section?->name ?? null,
            'periods' => $viewablePeriods->map(fn ($p) => [
                'id'   => $p->AcademicPeriodID,
                'name' => $p->academicPeriod->PeriodName,
            ])->values(),
            'subjects' => $tableData,
            'totals'   => $periodTotals,
        ];
    }

    /**
     * GET /api/students/national-id/{nationalId}
     * عرض بيانات الطالب ودرجاته بالرقم الوطني.
     *
     * Query params:
     *   ?academic_year_id=X  → يعرض قيد سنة محددة (اختياري، الافتراضي: أحدث قيد)
     */
    public function getStudentData($nationalId, Request $request): JsonResponse
    {
        $student = student::where('national_id', $nationalId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود',
            ], 404);
        }

        if ($student->status_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'تم حجب النتيجة من قبل ادارة المدرسة',
            ], 403);
        }

        // كل قيود الطالب (للسماح باختيار السنة من الواجهة)
        $allEnrollments = StudentEnrollment::with(['grade', 'academicYear'])
            ->where('student_id', $student->id)
            ->orderByDesc('academic_year_id')
            ->get();

        if ($allEnrollments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد قيد للطالب في أي سنة دراسية',
            ], 404);
        }

        // اختيار القيد المطلوب
        $academicYearId = $request->query('academic_year_id');
        $enrollment = $academicYearId
            ? $allEnrollments->firstWhere('academic_year_id', $academicYearId)
            : $allEnrollments->first(); // أحدث قيد

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد قيد للطالب في هذه السنة الدراسية',
            ], 404);
        }

        $marksData = $this->buildEnrollmentMarksData($enrollment);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                [
                    'student_id'  => $student->id,
                    'full_name'   => $student->full_name,
                    'national_id' => $student->national_id,
                    // قائمة بكل السنوات المتاحة لهذا الطالب لاستخدامها في الواجهة
                    'enrollments' => $allEnrollments->map(fn ($e) => [
                        'enrollment_id'  => $e->id,
                        'academic_year_id' => $e->academic_year_id,
                        'year_label'     => $e->academicYear->year_label ?? null,
                        'grade_name'     => $e->grade->name ?? null,
                    ])->values(),
                ],
                $marksData
            ),
        ], 200);
    }

    /**
     * GET /api/students
     */
    public function getAllStudents(): JsonResponse
    {
        $students = student::all();

        return response()->json([
            'success' => true,
            'data' => $students->map(fn ($s) => [
                'student_id'  => $s->id,
                'full_name'   => $s->full_name,
                'national_id' => $s->national_id,
            ]),
        ], 200);
    }

    /**
     * GET /api/students/{id}
     * عرض بيانات الطالب ودرجاته بالـ ID.
     *
     * Query params:
     *   ?academic_year_id=X  → يعرض قيد سنة محددة (اختياري، الافتراضي: أحدث قيد)
     */
    public function getStudentById($id, Request $request): JsonResponse
    {
        $student = student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود',
            ], 404);
        }

        if ($student->status_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'تم حجب النتيجة من قبل ادارة المدرسة',
            ], 403);
        }

        $allEnrollments = StudentEnrollment::with(['grade', 'academicYear'])
            ->where('student_id', $student->id)
            ->orderByDesc('academic_year_id')
            ->get();

        if ($allEnrollments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد قيد للطالب في أي سنة دراسية',
            ], 404);
        }

        $academicYearId = $request->query('academic_year_id');
        $enrollment = $academicYearId
            ? $allEnrollments->firstWhere('academic_year_id', $academicYearId)
            : $allEnrollments->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد قيد للطالب في هذه السنة الدراسية',
            ], 404);
        }

        $marksData = $this->buildEnrollmentMarksData($enrollment);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                [
                    'student_id'  => $student->id,
                    'full_name'   => $student->full_name,
                    'national_id' => $student->national_id,
                    'enrollments' => $allEnrollments->map(fn ($e) => [
                        'enrollment_id'    => $e->id,
                        'academic_year_id' => $e->academic_year_id,
                        'year_label'       => $e->academicYear->year_label ?? null,
                        'grade_name'       => $e->grade->name ?? null,
                    ])->values(),
                ],
                $marksData
            ),
        ], 200);
    }
}
