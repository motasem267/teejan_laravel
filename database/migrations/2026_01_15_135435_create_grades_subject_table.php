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
        Schema::create('grades_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subjectID');
            $table->unsignedBigInteger('gradeID');
            
            $table->foreign('subjectID')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('gradeID')->references('id')->on('grades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades_subject');
    }
};
