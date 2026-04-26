<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

// احصل على أول موظف (المسؤول)
$admin = Employee::first();

if (!$admin) {
    echo "✗ لم يتم العثور على أي موظف في قاعدة البيانات!\n";
    exit(1);
}

echo "سيتم منح صلاحيات التقارير والحضور للموظف: {$admin->name} (ID: {$admin->id})\n\n";

// احصل على الصلاحيات الجديدة
$reportPermissions = Permission::whereIn('name', [
    'attendance-report',
    'attendance-report.view',
    'expense-report',
    'expense-report.view',
    'salary-report',
    'salary-report.view',
    'revenue-report',
    'revenue-report.view',
])->pluck('id')->toArray();

$currentPermissions = $admin->permissions()->pluck('permissions.id')->toArray();

$granted = 0;

foreach ($reportPermissions as $permissionId) {
    if (!in_array($permissionId, $currentPermissions)) {
        DB::table('employee_permissions')->insert([
            'employee_id' => $admin->id,
            'permission_id' => $permissionId,
        ]);
        $granted++;
    }
}

echo "\n════════════════════════════════════════\n";
echo "✓ تم منح $granted صلاحية جديدة\n";
echo "○ " . count(array_intersect($reportPermissions, $currentPermissions)) . " صلاحية كانت موجودة مسبقاً\n";
echo "═ إجمالي صلاحيات التقارير: " . count($reportPermissions) . "\n";
echo "════════════════════════════════════════\n";
echo "\n✓ تم إضافة صلاحيات التقارير بنجاح!\n";
