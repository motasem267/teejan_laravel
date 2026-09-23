<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // العمود موجود أصلا ومستعمل فعليا في الإنتاج (academic_years::getActiveId())
        // — ما نلمسوش لو موجود، باش ما نبدلوش السنة النشطة الحالية بالغلط.
        if (Schema::hasColumn('academic_years', 'is_active')) {
            return;
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('year_label');
        });

        // جعل أول سنة دراسية هي السنة النشطة بشكل افتراضي
        DB::table('academic_years')->orderBy('id', 'asc')->limit(1)->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('academic_years', 'is_active')) {
            Schema::table('academic_years', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
