<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // كل خطوة تتحقق قبل ما تتنفذ — القاعدة الحقيقية اختلفت أكثر من مرة عما
        // كانت تفترضه هذي الميقريشن (عمود semester أصلا ماكانش موجود)، وممكن
        // جزء من الخطوات يكون تنفذ من محاولة سابقة فشلت في نص الطريق.

        // ترتيب مهم: نزيد الفهرس الجديد (وهو زادة يبدا بـ student_id) قبل ما نحذف
        // القديم، لأن unique_student_enrollment هو الفهرس الوحيد اللي يدعم
        // الـ Foreign Key student_enrollments_student_id_foreign — حذفه قبل ما
        // يكون فيه بديل يعطي خطأ "Cannot drop index ... needed in a foreign key
        // constraint".
        if (!Schema::hasIndex('student_enrollments', 'unique_student_grade_year')) {
            DB::statement('ALTER TABLE student_enrollments ADD UNIQUE KEY unique_student_grade_year (student_id, grade_id, academic_year_id)');
        }

        if (Schema::hasIndex('student_enrollments', 'unique_student_enrollment')) {
            DB::statement('ALTER TABLE student_enrollments DROP INDEX unique_student_enrollment');
        }

        if (Schema::hasColumn('student_enrollments', 'semester')) {
            DB::statement('ALTER TABLE student_enrollments DROP COLUMN semester');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // نفس المنطق بالعكس: نرجع العمود والفهرس القديم قبل ما نحذف الجديد.
        if (!Schema::hasColumn('student_enrollments', 'semester')) {
            DB::statement("ALTER TABLE student_enrollments ADD COLUMN semester ENUM('first', 'second', 'third') COMMENT 'الفصل الدراسي: first = الأول، second = الثاني، third = الثالث' AFTER academic_year_id");
        }

        if (!Schema::hasIndex('student_enrollments', 'unique_student_enrollment')) {
            DB::statement('ALTER TABLE student_enrollments ADD UNIQUE KEY unique_student_enrollment (student_id, grade_id, academic_year_id, semester)');
        }

        if (Schema::hasIndex('student_enrollments', 'unique_student_grade_year')) {
            DB::statement('ALTER TABLE student_enrollments DROP INDEX unique_student_grade_year');
        }
    }
};
