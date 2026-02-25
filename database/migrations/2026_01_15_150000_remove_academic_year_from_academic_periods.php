<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['AcademicYearID']);
            // Then drop column
            $table->dropColumn('AcademicYearID');
        });
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('AcademicYearID')->after('PeriodName');
            $table->foreign('AcademicYearID')->references('id')->on('academic_years')->onDelete('cascade');
        });
    }
};
