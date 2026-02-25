<?php

/**
 * إضافة صلاحية طباعة الجدول الدراسي ومنحها للمشرف
 * Run: php add_timetable_print_permission.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

try {
    DB::beginTransaction();

    echo "═══════════════════════════════════════════\n";
    echo " إضافة صلاحية طباعة الجدول الدراسي\n";
    echo "═══════════════════════════════════════════\n\n";

    // 1. إضافة الصلاحية الرئيسية
    $parent = Permission::firstOrCreate(
        ['name' => 'timetable-print'],
        ['label' => 'طباعة الجدول الدراسي', 'parent_id' => null]
    );
    echo "✓ الصلاحية الرئيسية: {$parent->label} (ID: {$parent->id})\n";

    // 2. إضافة الصلاحية الفرعية
    $viewPerm = Permission::firstOrCreate(
        ['name' => 'timetable-print.view'],
        ['label' => 'عرض وطباعة الجدول', 'parent_id' => $parent->id]
    );
    echo "✓ صلاحية العرض: {$viewPerm->label} (ID: {$viewPerm->id})\n";

    // 3. إيجاد المشرف
    $admin = Employee::find(9999)
        ?? Employee::where('email', 'admin@admin.com')->first()
        ?? Employee::orderBy('id')->first();

    if (!$admin) {
        echo "\n❌ لم يُعثر على أي موظف في قاعدة البيانات!\n";
        DB::rollBack();
        exit(1);
    }

    echo "\n✓ المشرف: {$admin->name} (ID: {$admin->id})\n";

    // 4. منح الصلاحيتين للمشرف
    $granted = 0;
    foreach ([$parent->id, $viewPerm->id] as $permId) {
        $exists = DB::table('employee_permissions')
            ->where('employee_id', $admin->id)
            ->where('permission_id', $permId)
            ->exists();

        if (!$exists) {
            DB::table('employee_permissions')->insert([
                'employee_id'   => $admin->id,
                'permission_id' => $permId,
            ]);
            $granted++;
        }
    }

    // 5. تسجيل في activity_logs
    ActivityLog::create([
        'user_id'     => $admin->id,
        'action'      => 'add-permission',
        'description' => 'Added timetable-print permission and granted to admin ID ' . $admin->id,
        'model_type'  => Permission::class,
        'model_id'    => $parent->id,
        'ip_address'  => '127.0.0.1',
        'user_agent'  => 'CLI Script',
    ]);

    DB::commit();

    echo "\n═══════════════════════════════════════════\n";
    if ($granted > 0) {
        echo "✓ تم منح {$granted} صلاحية جديدة للمشرف\n";
    } else {
        echo "○ المشرف يملك الصلاحيات مسبقاً\n";
    }
    echo "✓ تم تسجيل العملية في سجل النشاط\n";
    echo "═══════════════════════════════════════════\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
