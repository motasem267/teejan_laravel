<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * النسخة الأصلية كانت مكتوبة لنظام صلاحيات مختلف (جداول roles/role_has_permissions
     * وعمود guard_name) وهذا المشروع ما يستعملش هذا النظام أصلا — يستعمل جدول
     * permissions (name/label/parent_id) + employee_permissions مباشرة، بنفس
     * نمط add_attendance_report_permissions.php. أعدت كتابتها لتطابق النظام
     * الفعلي، وإلا كانت بش تطيح فورا (لا roles ولا guard_name موجودين).
     */
    public function up(): void
    {
        $permissions = [
            ['name' => 'expenses-report', 'label' => 'تقرير المصروفات'],
            ['name' => 'expenses-report.view', 'label' => 'عرض تقرير المصروفات'],
            ['name' => 'salaries-report', 'label' => 'تقرير المرتبات'],
            ['name' => 'salaries-report.view', 'label' => 'عرض تقرير المرتبات'],
            ['name' => 'revenue-report', 'label' => 'تقرير الإيرادات'],
            ['name' => 'revenue-report.view', 'label' => 'عرض تقرير الإيرادات'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                ...$permission,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['expenses-report', 'salaries-report', 'revenue-report'] as $group) {
            $parent = DB::table('permissions')->where('name', $group)->first();

            if ($parent) {
                DB::table('permissions')
                    ->where('name', $group . '.view')
                    ->update(['parent_id' => $parent->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'expenses-report',
            'expenses-report.view',
            'salaries-report',
            'salaries-report.view',
            'revenue-report',
            'revenue-report.view',
        ])->delete();
    }
};
