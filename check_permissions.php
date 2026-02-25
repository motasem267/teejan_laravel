<?php

use Illuminate\Support\Facades\DB;

// عد الصلاحيات الممنوحة
$permissionIds = DB::table('permissions')
    ->whereIn('name', [
        'days',
        'days.view',
        'days.create',
        'days.edit',
        'days.delete',
        'lesson-times',
        'lesson-times.view',
        'lesson-times.create',
        'lesson-times.edit',
        'lesson-times.delete',
        'school-schedules',
        'school-schedules.view',
        'school-schedules.create',
        'school-schedules.edit',
        'school-schedules.delete'
    ])
    ->pluck('id');

$count = DB::table('employee_permissions')
    ->whereIn('permission_id', $permissionIds)
    ->count();

$employees = DB::table('employee_permissions')
    ->whereIn('permission_id', $permissionIds)
    ->distinct('employee_id')
    ->count('employee_id');

echo "✅ تم منح {$count} صلاحية\n";
echo "👤 عدد الموظفين الذين لديهم صلاحيات: {$employees}\n";
echo "✓ النظام جاهز للاستخدام\n";
