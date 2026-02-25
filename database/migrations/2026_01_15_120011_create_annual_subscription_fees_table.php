<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_subscription_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->decimal('amount', 18, 2);
            $table->timestamps();
            
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            
            // Ensure unique combination of grade and academic year
            $table->unique(['grade_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_subscription_fees');
    }
};
