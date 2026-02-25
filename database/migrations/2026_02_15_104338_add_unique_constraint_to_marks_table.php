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
        Schema::table('marks', function (Blueprint $table) {
            // إضافة unique index لمنع تكرار الدرجات لنفس الطالب في نفس المادة والفترة والسنة
            $table->unique(
                ['student_id', 'subject_id', 'AcademicPeriodID', 'academic_year_id'],
                'unique_student_subject_period_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->dropUnique('unique_student_subject_period_year');
        });
    }
};
