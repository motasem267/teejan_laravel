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

echo "سيتم منح جميع الصلاحيات للموظف: {$admin->name} (ID: {$admin->id})\n\n";

// احصل على جميع الصلاحيات
$allPermissions = Permission::all();
$currentPermissions = $admin->permissions()->pluck('permissions.id')->toArray();

$granted = 0;

foreach ($allPermissions as $permission) {
    if (!in_array($permission->id, $currentPermissions)) {
        DB::table('employee_permissions')->insert([
            'employee_id' => $admin->id,
            'permission_id' => $permission->id,
        ]);
        $granted++;
    }
}

echo "\n════════════════════════════════════════\n";
echo "✓ تم منح $granted صلاحية جديدة\n";
echo "○ " . count($currentPermissions) . " صلاحية كانت موجودة مسبقاً\n";
echo "═ إجمالي الصلاحيات: " . ($granted + count($currentPermissions)) . "\n";
echo "════════════════════════════════════════\n";
echo "\n✓ الآن جميع الموارد يجب أن تظهر في الـ sidebar!\n";
