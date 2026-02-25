<?php

/**
 * Script to add reports permissions
 * Run: php add_reports_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "بدء إضافة صلاحيات التقارير...\n\n";

    // 1. إنشاء مجموعة صلاحيات التقارير
    $reportsParent = Permission::firstOrCreate(
        ['name' => 'reports'],
        ['label' => 'التقارير', 'parent_id' => null]
    );
    
    $reportsPermissions = [
        ['name' => 'reports.view', 'label' => 'عرض التقارير'],
        ['name' => 'reports.student-evaluations', 'label' => 'تقرير تقييمات الطلاب'],
        ['name' => 'reports.evaluation-monitor', 'label' => 'متابعة التقييمات'],
        ['name' => 'reports.export', 'label' => 'تصدير التقارير'],
    ];
    
    echo "إضافة صلاحيات التقارير:\n";
    foreach ($reportsPermissions as $perm) {
        Permission::firstOrCreate(
            ['name' => $perm['name']],
            ['label' => $perm['label'], 'parent_id' => $reportsParent->id]
        );
        echo "  ✓ {$perm['label']}\n";
    }

    // 2. إعطاء هذه الصلاحيات للمشرف
    $admin = Employee::find(9999);
    
    if ($admin) {
        echo "\nإضافة صلاحيات التقارير للمشرف...\n";
        
        $reportPermIds = Permission::whereIn('name', [
            'reports.view',
            'reports.student-evaluations',
            'reports.evaluation-monitor',
            'reports.export',
        ])->pluck('id')->toArray();
        
        $currentPermissions = $admin->permissions()->pluck('id')->toArray();
        $allPermissions = array_unique(array_merge($currentPermissions, $reportPermIds));
        
        $admin->permissions()->sync($allPermissions);
        
        echo "  ✓ تم إضافة " . count($reportPermIds) . " صلاحية للمشرف\n";
    }

    DB::commit();
    
    echo "\n✅ تم إضافة صلاحيات التقارير بنجاح!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ حدث خطأ: " . $e->getMessage() . "\n";
    exit(1);
}
