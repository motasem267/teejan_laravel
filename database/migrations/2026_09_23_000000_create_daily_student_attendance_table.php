<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_student_attendance')) {
            return;
        }

        Schema::create('daily_student_attendance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('first_check_in')->nullable();
            $table->dateTime('last_check_out')->nullable();
            $table->string('status', 30)->default('present');
            $table->timestamps();

            $table->unique(['student_id', 'date'], 'daily_student_attendance_unique_day');
            $table->index(['date', 'student_id'], 'daily_student_attendance_date_student_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_student_attendance');
    }
};
