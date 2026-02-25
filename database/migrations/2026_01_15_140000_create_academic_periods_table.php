<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id('AcademicPeriodID');
            $table->string('PeriodName', 100);
            $table->unsignedBigInteger('AcademicYearID');
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->boolean('IsActive')->default(true);
            
            $table->foreign('AcademicYearID')->references('id')->on('academic_years')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
