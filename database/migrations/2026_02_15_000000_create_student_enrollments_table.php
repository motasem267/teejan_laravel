<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('grade_id')
                  ->constrained('grades')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->timestamps();

            // إنشاء index فريد لضمان عدم تكرار القيد للطالب في نفس الصف والسنة
            $table->unique(['student_id', 'grade_id', 'academic_year_id'], 'unique_student_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
