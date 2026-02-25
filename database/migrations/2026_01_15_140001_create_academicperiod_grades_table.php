<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academicperiod_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('AcademicPeriodID');
            $table->unsignedBigInteger('GradeID');
            $table->boolean('IsViewed')->default(false);
            
            $table->foreign('AcademicPeriodID')->references('AcademicPeriodID')->on('academic_periods')->onDelete('cascade');
            $table->foreign('GradeID')->references('id')->on('grades')->onDelete('cascade');
            
            $table->unique(['AcademicPeriodID', 'GradeID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academicperiod_grades');
    }
};
