<?php

/**
 * نقل الصف والشعبة من جدول الطلبة إلى جدول قيد الطالب
 * 
 * الخطوات:
 * 1. إضافة عمود section_id إلى student_enrollments
 * 2. نسخ grade_id و section_id من students إلى student_enrollments
 * 3. حذف أعمدة grade_id و section_id من students
 */

// Bootstrap Laravel
require __DIR__.'/bootstrap/app.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    DB::beginTransaction();
    
    echo "=== بدء نقل الصف والشعبة من جدول الطلبة إلى قيد الطالب ===" . PHP_EOL . PHP_EOL;
    
    // 1. إضافة عمود section_id إلى student_enrollments
    echo "1. إضافة عمود section_id إلى جدول student_enrollments..." . PHP_EOL;
    if (!Schema::hasColumn('student_enrollments', 'section_id')) {
        DB::statement('ALTER TABLE student_enrollments ADD COLUMN section_id BIGINT UNSIGNED NULL AFTER grade_id');
        DB::statement('ALTER TABLE student_enrollments ADD CONSTRAINT fk_student_enrollments_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL');
        echo "   ✓ تم إضافة عمود section_id" . PHP_EOL;
    } else {
        echo "   → عمود section_id موجود مسبقاً" . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    // 2. نسخ البيانات من students إلى student_enrollments
    echo "2. نسخ البيانات من جدول students إلى student_enrollments..." . PHP_EOL;
    
    // جلب جميع القيود مع بيانات الطلبة
    $enrollments = DB::table('student_enrollments')
        ->join('students', 'student_enrollments.student_id', '=', 'students.id')
        ->select(
            'student_enrollments.id as enrollment_id',
            'students.grade_id as student_grade_id',
            'students.section_id as student_section_id',
            'student_enrollments.grade_id as enrollment_grade_id',
            'student_enrollments.section_id as enrollment_section_id'
        )
        ->get();
    
    $updatedCount = 0;
    foreach ($enrollments as $enrollment) {
        // تحديث القيد بالبيانات من جدول students
        $updateData = [];
        
        // إذا كان grade_id في enrollment فارغ أو مختلف، نحدثه
        if (empty($enrollment->enrollment_grade_id) || $enrollment->enrollment_grade_id != $enrollment->student_grade_id) {
            $updateData['grade_id'] = $enrollment->student_grade_id;
        }
        
        // إذا كان section_id في enrollment فارغ، نحدثه
        if (empty($enrollment->enrollment_section_id) && !empty($enrollment->student_section_id)) {
            $updateData['section_id'] = $enrollment->student_section_id;
        }
        
        if (!empty($updateData)) {
            DB::table('student_enrollments')
                ->where('id', $enrollment->enrollment_id)
                ->update($updateData);
            $updatedCount++;
        }
    }
    
    echo "   ✓ تم تحديث {$updatedCount} قيد" . PHP_EOL;
    echo PHP_EOL;
    
    // 3. حذف قيود المفاتيح الخارجية من students
    echo "3. حذف قيود المفاتيح الخارجية من جدول students..." . PHP_EOL;
    
    // حذف قيد المفتاح الخارجي للصف
    try {
        DB::statement('ALTER TABLE students DROP FOREIGN KEY students_grade_id_foreign');
        echo "   ✓ تم حذف قيد المفتاح الخارجي للصف" . PHP_EOL;
    } catch (Exception $e) {
        echo "   → قيد المفتاح الخارجي للصف غير موجود أو تم حذفه مسبقاً" . PHP_EOL;
    }
    
    // حذف قيد المفتاح الخارجي للشعبة
    try {
        DB::statement('ALTER TABLE students DROP FOREIGN KEY students_section_id_foreign');
        echo "   ✓ تم حذف قيد المفتاح الخارجي للشعبة" . PHP_EOL;
    } catch (Exception $e) {
        echo "   → قيد المفتاح الخارجي للشعبة غير موجود أو تم حذفه مسبقاً" . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    // 4. حذف أعمدة grade_id و section_id من students
    echo "4. حذف أعمدة grade_id و section_id من جدول students..." . PHP_EOL;
    
    if (Schema::hasColumn('students', 'grade_id')) {
        DB::statement('ALTER TABLE students DROP COLUMN grade_id');
        echo "   ✓ تم حذف عمود grade_id" . PHP_EOL;
    } else {
        echo "   → عمود grade_id غير موجود" . PHP_EOL;
    }
    
    if (Schema::hasColumn('students', 'section_id')) {
        DB::statement('ALTER TABLE students DROP COLUMN section_id');
        echo "   ✓ تم حذف عمود section_id" . PHP_EOL;
    } else {
        echo "   → عمود section_id غير موجود" . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    DB::commit();
    
    echo "=== تمت العملية بنجاح! ===" . PHP_EOL;
    echo PHP_EOL;
    echo "النتيجة:" . PHP_EOL;
    echo "- تم نقل الصف والشعبة من جدول students إلى student_enrollments" . PHP_EOL;
    echo "- تم حذف أعمدة grade_id و section_id من جدول students" . PHP_EOL;
    echo "- الآن الصف والشعبة موجودان فقط في جدول قيد الطالب (student_enrollments)" . PHP_EOL;
    
} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL;
    echo "❌ حدث خطأ: " . $e->getMessage() . PHP_EOL;
    echo "تم التراجع عن جميع التغييرات." . PHP_EOL;
    exit(1);
}
