<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== فحص تقييمات المعلمات باستخدام Laravel ===\n\n";

try {
    echo "1. البحث عن المعلمة 'مفيدة':\n";
    
    $teachers = DB::table('teachers')->where('name', 'LIKE', '%مفيدة%')->get();
    
    if ($teachers->isEmpty()) {
        echo "   لم يتم العثور على المعلمة مفيدة. البحث عن جميع المعلمات:\n";
        $allTeachers = DB::table('teachers')->get();
        foreach ($allTeachers as $teacher) {
            echo "   - {$teacher->name} (ID: {$teacher->id})\n";
        }
    } else {
        foreach ($teachers as $teacher) {
            echo "   ✓ وجدت المعلمة: {$teacher->name} (ID: {$teacher->id})\n\n";
            
            echo "2. تقييمات هذه المعلمة:\n";
            
            $evaluations = DB::table('student_evaluation_records as ser')
                ->leftJoin('students as s', 'ser.student_id', '=', 's.id')
                ->leftJoin('subjects as sub', 'ser.subject_id', '=', 'sub.id')
                ->where('ser.teacher_id', $teacher->id)
                ->select([
                    'ser.*', 
                    's.name as student_name',
                    'sub.name as subject_name'
                ])
                ->orderBy('ser.subject_id')
                ->orderBy('ser.week')
                ->get();
            
            if ($evaluations->isEmpty()) {
                echo "   لا توجد تقييمات لهذه المعلمة\n";
            } else {
                echo "   عدد التقييمات الإجمالي: " . $evaluations->count() . "\n\n";
                
                // تجميع حسب المادة
                $bySubject = $evaluations->groupBy('subject_id');
                
                foreach ($bySubject as $subjectId => $subjectEvals) {
                    $subjectName = $subjectEvals->first()->subject_name;
                    $uniqueStudents = $subjectEvals->pluck('student_id')->unique();
                    
                    echo "   🔸 المادة: {$subjectName} (ID: {$subjectId})\n";
                    echo "      عدد الطلاب المقيمين: " . $uniqueStudents->count() . "\n";
                    echo "      عدد التقييمات: " . $subjectEvals->count() . "\n";
                    
                    echo "      الطلاب المقيمين:\n";
                    $studentGroups = $subjectEvals->groupBy('student_id');
                    foreach ($studentGroups as $studentId => $studentEvals) {
                        $studentName = $studentEvals->first()->student_name;
                        echo "        • {$studentName} (ID: {$studentId}) - " . $studentEvals->count() . " تقييم\n";
                    }
                    
                    echo "      تفاصيل التقييمات:\n";
                    foreach ($subjectEvals as $eval) {
                        echo "        - {$eval->student_name} | أسبوع: {$eval->week} | شهر: {$eval->month} | {$eval->created_at}\n";
                    }
                    echo "\n";
                }
            }
        }
    }
    
    // إحصائية إضافية
    echo "3. إحصائية عامة:\n";
    $totalEvaluations = DB::table('student_evaluation_records')->count();
    $totalTeachers = DB::table('teachers')->count();
    $totalSubjects = DB::table('subjects')->count();
    
    echo "   إجمالي التقييمات في النظام: {$totalEvaluations}\n";
    echo "   إجمالي المعلمات: {$totalTeachers}\n";
    echo "   إجمالي المواد: {$totalSubjects}\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "تفاصيل الخطأ: " . $e->getTraceAsString() . "\n";
}