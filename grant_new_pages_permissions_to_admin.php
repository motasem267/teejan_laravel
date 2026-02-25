<?php

/**
 * Script to grant all new page permissions to admin
 * Run: php grant_new_pages_permissions_to_admin.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "بدء إعطاء صلاحيات الصفحات الجديدة للمشرف...\n\n";

    // Find admin employee (you can change the condition to find your admin)
    $admin = Employee::where('email', 'admin@admin.com')->first();
    
    if (!$admin) {
        // Try to find the first employee with admin in name
        $admin = Employee::where('name', 'LIKE', '%admin%')->first();
    }
    
    if (!$admin) {
        // Try to get employee with ID 9999 (from the error)
        $admin = Employee::find(9999);
    }

    if (!$admin) {
        echo "❌ لم يتم العثور على حساب المشرف!\n";
        echo "يرجى تحديد ID الموظف المشرف:\n";
        echo "الموظفون المتاحون:\n";
        $employees = Employee::limit(10)->get();
        foreach ($employees as $emp) {
            echo "  ID: {$emp->id} - {$emp->name} ({$emp->email})\n";
        }
        DB::rollBack();
        exit(1);
    }

    echo "تم العثور على المشرف: {$admin->name} (ID: {$admin->id})\n\n";

    // Get all new permissions
    $newPermissions = [
        'assign-permissions.view',
        'assign-permissions.manage',
        'grade-promotion.view',
        'grade-promotion.promote',
        'student-evaluation.view',
        'student-evaluation.create',
        'student-evaluation.edit',
        'student-evaluation.delete',
        'student-evaluation.export-pdf',
        'employees.change-password',
        'student_enrollments.view',
        'student_enrollments.create',
        'student_enrollments.edit',
        'student_enrollments.delete',
        'student_enrollments.promote',
    ];

    $permissionsToAdd = [];
    echo "الصلاحيات المراد إضافتها:\n";
    foreach ($newPermissions as $permName) {
        $permission = Permission::where('name', $permName)->first();
        if ($permission) {
            $permissionsToAdd[] = $permission->id;
            echo "  ✓ {$permission->label} ({$permName})\n";
        } else {
            echo "  ✗ لم يتم العثور على: {$permName}\n";
        }
    }

    if (empty($permissionsToAdd)) {
        echo "\n❌ لا توجد صلاحيات لإضافتها!\n";
        DB::rollBack();
        exit(1);
    }

    // Get current permissions
    $currentPermissions = $admin->permissions()->pluck('id')->toArray();

    // Merge with new permissions (avoid duplicates)
    $allPermissions = array_unique(array_merge($currentPermissions, $permissionsToAdd));

    // Sync permissions
    $admin->permissions()->sync($allPermissions);

    // Reload to confirm
    $admin->load('permissions');

    $addedCount = count($permissionsToAdd) - count(array_intersect($currentPermissions, $permissionsToAdd));

    DB::commit();
    
    echo "\n✅ تم إضافة {$addedCount} صلاحية جديدة للمشرف {$admin->name}!\n";
    echo "إجمالي الصلاحيات الآن: " . $admin->permissions()->count() . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ حدث خطأ: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
