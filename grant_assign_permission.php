<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$adminId = 9999;

// الحصول على صلاحية assign-permissions
$permissionId = DB::table('permissions')
    ->where('name', 'assign-permissions.view')
    ->value('id');

if ($permissionId) {
    // التحقق إذا كانت الصلاحية موجودة بالفعل
    $exists = DB::table('employee_permissions')
        ->where('employee_id', $adminId)
        ->where('permission_id', $permissionId)
        ->exists();
    
    if ($exists) {
        echo "✓ المستخدم 9999 لديه بالفعل صلاحية 'إعطاء الصلاحيات'\n";
    } else {
        DB::table('employee_permissions')->insert([
            'employee_id' => $adminId,
            'permission_id' => $permissionId,
        ]);
        echo "✅ تم منح صلاحية 'إعطاء الصلاحيات' للمستخدم 9999\n";
    }
} else {
    echo "❌ لم يتم العثور على صلاحية 'assign-permissions.view'\n";
}
