<?php

/**
 * Script to add permissions for new pages
 * Run: php add_new_pages_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "بدء إضافة صلاحيات الصفحات الجديدة...\n\n";

    // 1. صلاحيات صفحة إعطاء الصلاحيات (Assign Permissions)
    echo "1. إضافة صلاحيات صفحة إعطاء الصلاحيات...\n";
    $assignPermParent = Permission::firstOrCreate(
        ['name' => 'assign-permissions'],
        ['label' => 'إعطاء الصلاحيات', 'parent_id' => null]
    );
    
    $assignPermissions = [
        ['name' => 'assign-permissions.view', 'label' => 'عرض صفحة إعطاء الصلاحيات'],
        ['name' => 'assign-permissions.manage', 'label' => 'إدارة صلاحيات الموظفين'],
    ];
    
    foreach ($assignPermissions as $perm) {
        Permission::firstOrCreate(
            ['name' => $perm['name']],
            ['label' => $perm['label'], 'parent_id' => $assignPermParent->id]
        );
        echo "  ✓ تم إضافة: {$perm['label']}\n";
    }

    // 2. صلاحيات صفحة ترحيل الطلبة (Grade Promotion)
    echo "\n2. إضافة صلاحيات صفحة ترحيل الطلبة...\n";
    $gradePromotionParent = Permission::firstOrCreate(
        ['name' => 'grade-promotion'],
        ['label' => 'ترحيل الطلبة', 'parent_id' => null]
    );
    
    $gradePromotionPermissions = [
        ['name' => 'grade-promotion.view', 'label' => 'عرض صفحة ترحيل الطلبة'],
        ['name' => 'grade-promotion.promote', 'label' => 'تنفيذ عملية ترحيل الطلبة'],
    ];
    
    foreach ($gradePromotionPermissions as $perm) {
        Permission::firstOrCreate(
            ['name' => $perm['name']],
            ['label' => $perm['label'], 'parent_id' => $gradePromotionParent->id]
        );
        echo "  ✓ تم إضافة: {$perm['label']}\n";
    }

    // 3. تحديث صلاحيات تقييم الطلاب (Student Evaluation) - إن لم تكن موجودة
    echo "\n3. التحقق من صلاحيات تقييم الطلاب...\n";
    $studentEvalParent = Permission::firstOrCreate(
        ['name' => 'student-evaluation'],
        ['label' => 'تقييم الطلاب', 'parent_id' => null]
    );
    
    $studentEvalPermissions = [
        ['name' => 'student-evaluation.view', 'label' => 'عرض تقييم الطلاب'],
        ['name' => 'student-evaluation.create', 'label' => 'إضافة تقييم الطلاب'],
        ['name' => 'student-evaluation.edit', 'label' => 'تعديل تقييم الطلاب'],
        ['name' => 'student-evaluation.delete', 'label' => 'حذف تقييم الطلاب'],
        ['name' => 'student-evaluation.export-pdf', 'label' => 'تصدير التقييمات PDF'],
    ];
    
    foreach ($studentEvalPermissions as $perm) {
        Permission::firstOrCreate(
            ['name' => $perm['name']],
            ['label' => $perm['label'], 'parent_id' => $studentEvalParent->id]
        );
        echo "  ✓ تم إضافة/تحديث: {$perm['label']}\n";
    }

    // 4. صلاحيات تغيير كلمة المرور
    echo "\n4. التحقق من صلاحيات تغيير كلمة المرور...\n";
    $employeesParent = Permission::where('name', 'employees')->first();
    if ($employeesParent) {
        Permission::firstOrCreate(
            ['name' => 'employees.change-password'],
            ['label' => 'تغيير كلمة المرور', 'parent_id' => $employeesParent->id]
        );
        echo "  ✓ تم إضافة/تحديث: تغيير كلمة المرور\n";
    }

    // 5. صلاحيات تسجيل الطلاب (Student Enrollments)
    echo "\n5. إضافة صلاحيات تسجيل الطلاب...\n";
    $studentEnrollParent = Permission::firstOrCreate(
        ['name' => 'student_enrollments'],
        ['label' => 'تسجيل الطلاب', 'parent_id' => null]
    );
    
    $studentEnrollPermissions = [
        ['name' => 'student_enrollments.view', 'label' => 'عرض تسجيل الطلاب'],
        ['name' => 'student_enrollments.create', 'label' => 'إضافة تسجيل الطلاب'],
        ['name' => 'student_enrollments.edit', 'label' => 'تعديل تسجيل الطلاب'],
        ['name' => 'student_enrollments.delete', 'label' => 'حذف تسجيل الطلاب'],
        ['name' => 'student_enrollments.promote', 'label' => 'ترحيل الطلاب'],
    ];
    
    foreach ($studentEnrollPermissions as $perm) {
        Permission::firstOrCreate(
            ['name' => $perm['name']],
            ['label' => $perm['label'], 'parent_id' => $studentEnrollParent->id]
        );
        echo "  ✓ تم إضافة: {$perm['label']}\n";
    }

    DB::commit();
    
    echo "\n✅ تم إضافة جميع صلاحيات الصفحات الجديدة بنجاح!\n";
    echo "\nيمكنك الآن استخدام هذه الصلاحيات في صفحة إعطاء الصلاحيات.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ حدث خطأ: " . $e->getMessage() . "\n";
    exit(1);
}
