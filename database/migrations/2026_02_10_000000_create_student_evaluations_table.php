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
        Schema::create('student_evaluations', function (Blueprint $table) {
            $table->id();

            // رقم الطالب
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // رقم السؤال
            $table->foreignId('question_id')
                ->constrained('evaluation_questions')
                ->cascadeOnDelete();

            // رقم الإجابة
            $table->foreignId('answer_id')
                ->constrained('evaluation_answers')
                ->cascadeOnDelete();

            // رقم الشهر
            $table->tinyInteger('month')->unsigned();

            // رقم الأسبوع
            $table->tinyInteger('week')->unsigned();

            // رقم السنة الدراسية
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            // رقم المعلم/ة
            $table->foreignId('teacher_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // تاريخ الإنشاء - يخزن تلقائياً
            $table->timestamp('created_at')->useCurrent();

            // تاريخ التحديث (اختياري)
            $table->timestamp('updated_at')->nullable();

            // فهرس لتحسين الأداء
            $table->index(['student_id', 'academic_year_id', 'month', 'week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_evaluations');
    }
};
