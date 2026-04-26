<?php
/**
 * Script to add Financial Reports Permissions and grant them to admin
 * Run: php grant_financial_reports_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Parent permission for reports group
    $parentPermissionId = DB::table('permissions')->insertOrIgnore([
        'name' => 'reports',
        'label' => 'التقارير المالية',
        'parent_id' => null,
    ]);

    // Get the parent ID (if it was inserted or if it already exists)
    $parentId = DB::table('permissions')->where('name', 'reports')->whereNull('parent_id')->first()?->id;

    if (!$parentId) {
        echo "❌ Error: Could not create or find reports parent permission\n";
        exit(1);
    }

    // Financial Reports Permissions with descriptions
    $permissions = [
        'expenses-report.view' => 'عرض تقرير المصاريف',
        'salaries-report.view' => 'عرض تقرير الرواتب',
        'revenue-report.view' => 'عرض تقرير الإيرادات',
    ];

    $permissionIds = [];

    foreach ($permissions as $permission => $label) {
        $result = DB::table('permissions')->insertOrIgnore([
            'name' => $permission,
            'label' => $label,
            'parent_id' => $parentId,
        ]);

        // Get the permission ID
        $id = DB::table('permissions')->where('name', $permission)->first()?->id;
        if ($id) {
            $permissionIds[] = $id;
            echo "✓ تم إضافة الصلاحية: $permission ($label) - ID: $id\n";
        }
    }

    // Find admin employee (ID: 9999 or the one with admin role)
    $adminEmployee = DB::table('employees')
        ->where('id', 9999)
        ->orWhere('name', 'LIKE', '%admin%')
        ->first();

    if (!$adminEmployee) {
        // Get the first employee as admin (fallback)
        $adminEmployee = DB::table('employees')->first();
    }

    if (!$adminEmployee) {
        echo "❌ Error: Could not find admin employee\n";
        exit(1);
    }

    $adminId = $adminEmployee->id;
    echo "\n👤 منح الصلاحيات للموظف (الإدمن): ID: $adminId\n";

    // Grant all permissions to admin
    $grantedCount = 0;
    foreach ($permissionIds as $permId) {
        $result = DB::table('employee_permissions')->insertOrIgnore([
            'employee_id' => $adminId,
            'permission_id' => $permId,
        ]);

        if ($result) {
            $grantedCount++;
            echo "  ✓ تم منح الصلاحية - Permission ID: $permId\n";
        }
    }

    // Also grant parent permission
    DB::table('employee_permissions')->insertOrIgnore([
        'employee_id' => $adminId,
        'permission_id' => $parentId,
    ]);

    echo "\n✅ تم إضافة جميع صلاحيات التقارير المالية بنجاح!\n";
    echo "📊 عدد الصلاحيات الممنوحة: " . ($grantedCount + 1) . "\n";

} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

