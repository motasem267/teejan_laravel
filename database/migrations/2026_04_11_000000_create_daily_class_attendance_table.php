<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الجدول أصلا موجود ومستعمل فعليا في الإنتاج (خط أنابيب الحضور شغال عليه)
        if (Schema::hasTable('daily_class_attendance')) {
            return;
        }

        Schema::create('daily_class_attendance', function (Blueprint $table): void {
            $table->id();
            // employees.id هو varchar(50) فعليا (موظفين بمعرّفات نصية، Employee::$incrementing = false)
            $table->string('employee_id', 50);
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status', 30)->default('pending');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->unique(['employee_id', 'date', 'start_time', 'end_time'], 'daily_class_attendance_unique_session');
            $table->index(['date', 'employee_id'], 'daily_class_attendance_date_employee_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_class_attendance');
    }
};