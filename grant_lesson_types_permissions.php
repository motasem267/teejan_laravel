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

echo "سيتم منح صلاحيات أنواع الحصص للموظف: {$admin->name} (ID: {$admin->id})\n\n";

// احصل على صلاحيات أنواع الحصص
$parent = Permission::where('name', 'lesson_types')->first();
$permissions = Permission::where('parent_id', $parent->id)->pluck('id')->toArray();

$granted = 0;
foreach ($permissions as $permissionId) {
    if (!$admin->permissions()->where('permission_id', $permissionId)->exists()) {
        DB::table('employee_permissions')->insert([
            'employee_id' => $admin->id,
            'permission_id' => $permissionId,
        ]);
        $granted++;
    }
}

echo "\n✓ تم منح $granted صلاحية لأنواع الحصص\n";
echo "✓ الآن ستظهر في صفحة إعطاء الصلاحيات\n";
