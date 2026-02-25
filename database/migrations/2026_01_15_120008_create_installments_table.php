<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('installment_type_id');
            $table->decimal('amount', 18, 2);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('payment_type_id');
            $table->string('academic_year', 100)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade');
            $table->foreign('installment_type_id')->references('id')->on('installment_types')->onDelete('restrict');
            $table->foreign('payment_type_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
