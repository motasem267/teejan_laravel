<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// منح صلاحية "إعطاء الصلاحيات" للمسؤول (ID: 9999)
$assignPermissionsParentId = DB::table('permissions')
    ->where('name', 'assign-permissions')
    ->whereNull('parent_id')
    ->value('id');

if ($assignPermissionsParentId) {
    $permissionIds = DB::table('permissions')
        ->where(function($query) use ($assignPermissionsParentId) {
            $query->where('id', $assignPermissionsParentId)
                  ->orWhere('parent_id', $assignPermissionsParentId);
        })
        ->pluck('id');
    
    foreach ($permissionIds as $permissionId) {
        DB::table('employee_permissions')->insertOrIgnore([
            'employee_id' => 9999,
            'permission_id' => $permissionId,
        ]);
    }
    
    echo "✅ تم منح صلاحية 'إعطاء الصلاحيات' للمسؤول (ID: 9999)\n";
    echo "عدد الصلاحيات الممنوحة: " . count($permissionIds) . "\n";
} else {
    echo "❌ لم يتم العثور على صلاحية 'إعطاء الصلاحيات'\n";
}
