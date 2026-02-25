<?php

/**
 * Script لإضافة صلاحيات قيد الطلبة (Student Enrollments)
 * 
 * تشغيل: php add_student_enrollments_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "🚀 بدء إضافة صلاحيات قيد الطلبة...\n\n";

DB::beginTransaction();

try {
    $permissions = [
        [
            'name' => 'student_enrollments.view',
            'label' => 'عرض قيد الطلبة',
            'category' => 'إدارة الطلاب',
            'description' => 'القدرة على عرض سجلات قيد الطلبة'
        ],
        [
            'name' => 'student_enrollments.create',
            'label' => 'إضافة قيد طالب',
            'category' => 'إدارة الطلاب',
            'description' => 'القدرة على تسجيل الطلبة في الصفوف والسنوات الدراسية'
        ],
        [
            'name' => 'student_enrollments.update',
            'label' => 'تعديل قيد طالب',
            'category' => 'إدارة الطلاب',
            'description' => 'القدرة على تعديل سجلات قيد الطلبة'
        ],
        [
            'name' => 'student_enrollments.delete',
            'label' => 'حذف قيد طالب',
            'category' => 'إدارة الطلاب',
            'description' => 'القدرة على حذف سجلات قيد الطلبة'
        ],
        [
            'name' => 'student_enrollments.promote',
            'label' => 'ترحيل الطلبة بالصف',
            'category' => 'إدارة الطلاب',
            'description' => 'القدرة على ترحيل مجموعة من الطلبة من صف إلى صف آخر'
        ],
    ];

    $addedCount = 0;
    $skippedCount = 0;

    foreach ($permissions as $permissionData) {
        $exists = Permission::where('name', $permissionData['name'])->exists();
        
        if (!$exists) {
            Permission::create($permissionData);
            echo "✅ تمت إضافة: {$permissionData['label']} ({$permissionData['name']})\n";
            $addedCount++;
        } else {
            echo "⏭️  موجودة مسبقاً: {$permissionData['label']} ({$permissionData['name']})\n";
            $skippedCount++;
        }
    }

    DB::commit();

    echo "\n✨ اكتمل! تمت إضافة {$addedCount} صلاحية جديدة، وتم تخطي {$skippedCount} صلاحية موجودة مسبقاً.\n";
    echo "\n💡 نصيحة: استخدم grant_admin_permission.php لمنح هذه الصلاحيات للمدير.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}
