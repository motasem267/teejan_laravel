<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مرآة حضور المعلمين من جهاز الاستقبال (Reception) — teejan_attendence يكتب
 * فيه (مصدره الأساسي هناك)، هذا بس نسخة قراءة هنا لتقارير البرودكشن.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_teacher_reception_attendance')) {
            return;
        }

        Schema::create('daily_teacher_reception_attendance', function (Blueprint $table): void {
            $table->id();
            // employees.id هو varchar(50) فعليا
            $table->string('employee_id', 50);
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('first_check_in')->nullable();
            $table->dateTime('last_check_out')->nullable();
            $table->string('status', 30)->default('present');
            $table->timestamps();

            $table->unique(['employee_id', 'date'], 'daily_teacher_reception_attendance_unique_day');
            $table->index(['date', 'employee_id'], 'daily_teacher_reception_attendance_date_employee_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_teacher_reception_attendance');
    }
};
