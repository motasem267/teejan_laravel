<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('employee_permissions')) {
            return;
        }

        $now = now();
        $parentName = 'teacher-classes';

        // Ensure parent permission exists.
        $parentId = DB::table('permissions')->where('name', $parentName)->value('id');
        if (! $parentId) {
            $parentId = DB::table('permissions')->insertGetId([
                'name' => $parentName,
                'label' => 'معلمي الفصول',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $actions = [
            'view' => 'عرض معلمي الفصول',
            'create' => 'إضافة معلم فصل',
            'edit' => 'تعديل معلم فصل',
            'delete' => 'حذف معلم فصل',
        ];

        $permissionIds = [];

        foreach ($actions as $suffix => $label) {
            $name = "$parentName.$suffix";
            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'label' => $label,
                    'parent_id' => $parentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $permissionIds[] = $id;
        }

        // Attach all employees to these permissions (skip if already attached).
        $employeeIds = DB::table('employees')->pluck('id');

        foreach ($employeeIds as $employeeId) {
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('employee_permissions')
                    ->where('employee_id', $employeeId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $exists) {
                    DB::table('employee_permissions')->insert([
                        'employee_id' => $employeeId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('employee_permissions')) {
            return;
        }

        $parentName = 'teacher-classes';
        $names = [
            $parentName,
            "$parentName.view",
            "$parentName.create",
            "$parentName.edit",
            "$parentName.delete",
        ];

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('employee_permissions')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }
    }
};
