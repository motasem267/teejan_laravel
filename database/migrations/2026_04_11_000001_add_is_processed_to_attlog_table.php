<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attlog') || Schema::hasColumn('attlog', 'is_processed')) {
            return;
        }

        Schema::table('attlog', function (Blueprint $table): void {
            $table->boolean('is_processed')->default(false)->index()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attlog') || ! Schema::hasColumn('attlog', 'is_processed')) {
            return;
        }

        Schema::table('attlog', function (Blueprint $table): void {
            $table->dropIndex(['is_processed']);
            $table->dropColumn('is_processed');
        });
    }
};