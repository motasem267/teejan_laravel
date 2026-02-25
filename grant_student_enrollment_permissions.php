<?php

/**
 * Script لمنح كل صلاحيات قيد الطلبة لموظف معين
 * 
 * تشغيل: php grant_student_enrollment_permissions.php <employee_id>
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

$employeeId = $argv[1] ?? 9999; // استخدم employee_id من الـ arguments أو القيمة الافتراضية 9999

echo "🚀 منح صلاحيات قيد الطلبة للموظف ID: {$employeeId}...\n\n";

$employee = Employee::find($employeeId);

if (!$employee) {
    echo "❌ الموظف برقم {$employeeId} غير موجود!\n";
    exit(1);
}

echo "👤 الموظف: {$employee->name} ({$employee->email})\n\n";

$permissions = Permission::where('name', 'LIKE', 'student_enrollments.%')->get();

if ($permissions->isEmpty()) {
    echo "❌ لم يتم العثور على صلاحيات قيد الطلبة! قم بتشغيل add_student_enrollments_permissions.php أولاً.\n";
    exit(1);
}

DB::beginTransaction();

try {
    $grantedCount = 0;
    
    foreach ($permissions as $permission) {
        $exists = DB::table('employee_permissions')
            ->where('employee_id', $employeeId)
            ->where('permission_id', $permission->id)
            ->exists();
        
        if (!$exists) {
            DB::table('employee_permissions')->insert([
                'employee_id' => $employeeId,
                'permission_id' => $permission->id,
            ]);
            echo "✅ تم منح: {$permission->label}\n";
            $grantedCount++;
        } else {
            echo "⏭️  موجودة مسبقاً: {$permission->label}\n";
        }
    }
    
    DB::commit();
    
    echo "\n✨ تم منح {$grantedCount} صلاحية جديدة للموظف {$employee->name}!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}
