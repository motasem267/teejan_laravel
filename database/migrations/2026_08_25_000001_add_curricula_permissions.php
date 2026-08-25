<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'curricula', 'label' => 'المناهج الدراسية'],
            ['name' => 'curricula.view', 'label' => 'عرض المناهج الدراسية'],
            ['name' => 'curricula.create', 'label' => 'إضافة المناهج الدراسية'],
            ['name' => 'curricula.edit', 'label' => 'تعديل المناهج الدراسية'],
            ['name' => 'curricula.delete', 'label' => 'حذف المناهج الدراسية'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                ...$permission,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $parent = DB::table('permissions')->where('name', 'curricula')->first();

        if ($parent) {
            DB::table('permissions')
                ->whereIn('name', ['curricula.view', 'curricula.create', 'curricula.edit', 'curricula.delete'])
                ->update(['parent_id' => $parent->id]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'curricula',
            'curricula.view',
            'curricula.create',
            'curricula.edit',
            'curricula.delete',
        ])->delete();
    }
};
