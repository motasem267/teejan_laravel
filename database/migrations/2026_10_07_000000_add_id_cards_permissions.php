<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'id-cards'],
            ['label' => 'بطاقات التعريف', 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()],
        );

        $parentId = DB::table('permissions')->where('name', 'id-cards')->value('id');

        $children = [
            ['name' => 'id-cards.students', 'label' => 'إصدار بطاقات الطلبة'],
            ['name' => 'id-cards.employees', 'label' => 'إصدار بطاقات الموظفين'],
        ];

        foreach ($children as $child) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $child['name']],
                ['label' => $child['label'], 'parent_id' => $parentId, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['id-cards', 'id-cards.students', 'id-cards.employees'])
            ->pluck('id');

        DB::table('employee_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
