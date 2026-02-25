<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->foreignId('emp_type_id')->nullable()->constrained('employee_types')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('employee_statuses')->nullOnDelete();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('password');
            $table->foreignId('salary_by')->nullable()->constrained('salary_types')->nullOnDelete();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
