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
        // استخدام SQL مباشر لحذف القيود والعمود
        DB::statement('ALTER TABLE student_enrollments DROP INDEX unique_student_enrollment');
        DB::statement('ALTER TABLE student_enrollments DROP COLUMN semester');
        DB::statement('ALTER TABLE student_enrollments ADD UNIQUE KEY unique_student_grade_year (student_id, grade_id, academic_year_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE student_enrollments DROP INDEX unique_student_grade_year');
        DB::statement("ALTER TABLE student_enrollments ADD COLUMN semester ENUM('first', 'second', 'third') COMMENT 'الفصل الدراسي: first = الأول، second = الثاني، third = الثالث' AFTER academic_year_id");
        DB::statement('ALTER TABLE student_enrollments ADD UNIQUE KEY unique_student_enrollment (student_id, grade_id, academic_year_id, semester)');
    }
};
