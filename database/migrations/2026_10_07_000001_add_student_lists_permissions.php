<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'student-lists'],
            ['label' => 'قوائم الطلبة', 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()],
        );

        $parentId = DB::table('permissions')->where('name', 'student-lists')->value('id');

        DB::table('permissions')->updateOrInsert(
            ['name' => 'student-lists.export'],
            ['label' => 'استخراج قوائم الطلبة', 'parent_id' => $parentId, 'created_at' => now(), 'updated_at' => now()],
        );
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['student-lists', 'student-lists.export'])
            ->pluck('id');

        DB::table('employee_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
