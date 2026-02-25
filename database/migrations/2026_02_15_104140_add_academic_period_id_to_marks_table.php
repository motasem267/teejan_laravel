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
            // إضافة عمود AcademicPeriodID إذا لم يكن موجوداً
            if (!Schema::hasColumn('marks', 'AcademicPeriodID')) {
                $table->foreignId('AcademicPeriodID')
                    ->after('academic_year_id')
                    ->constrained('academic_periods', 'AcademicPeriodID')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            if (Schema::hasColumn('marks', 'AcademicPeriodID')) {
                $table->dropForeign(['AcademicPeriodID']);
                $table->dropColumn('AcademicPeriodID');
            }
        });
    }
};
