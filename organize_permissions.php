<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use Illuminate\Support\Facades\DB;

// الموارد التي تحتاج إلى تنظيم
$resources = [
    'annual_subscription_fees' => 'الاشتراكات السنوية',
    'week-results' => 'نتائج الأسابيع',
    'installment_types' => 'أنواع الأقساط',
    'expenses_types' => 'أنواع المصروفات',
    'bonus_types' => 'أنواع العلاوات',
    'deduction_types' => 'أنواع الخصومات',
];

echo "بدء تنظيم الصلاحيات...\n\n";

foreach ($resources as $resourceName => $label) {
    echo "معالجة: $label ($resourceName)\n";
    
    // 1. إنشاء الصلاحية الرئيسية
    $parent = Permission::firstOrCreate(
        ['name' => $resourceName],
        ['label' => $label, 'parent_id' => null]
    );
    
    if ($parent->wasRecentlyCreated) {
        echo "  ✓ تم إنشاء الصلاحية الرئيسية\n";
    } else {
        echo "  ○ الصلاحية الرئيسية موجودة مسبقاً (ID: {$parent->id})\n";
    }
    
    // 2. البحث عن الصلاحيات الفرعية وربطها
    $actions = ['view-any', 'view', 'create', 'update', 'delete'];
    
    foreach ($actions as $action) {
        $childName = "$resourceName-$action";
        $child = Permission::where('name', $childName)->first();
        
        if ($child) {
            if ($child->parent_id !== $parent->id) {
                $child->parent_id = $parent->id;
                $child->name = "$resourceName.$action"; // تنسيق الاسم
                $child->label = match($action) {
                    'view-any' => "عرض $label",
                    'view' => "عرض $label",
                    'create' => "إضافة $label",
                    'update' => "تعديل $label",
                    'delete' => "حذف $label",
                };
                $child->save();
                echo "  ✓ تم ربط وتحديث: $childName -> $resourceName.$action\n";
            } else {
                echo "  ○ الصلاحية مربوطة مسبقاً: $childName\n";
            }
        } else {
            // إنشاء الصلاحية الفرعية إذا لم تكن موجودة
            $newChild = Permission::create([
                'name' => "$resourceName.$action",
                'label' => match($action) {
                    'view-any', 'view' => "عرض $label",
                    'create' => "إضافة $label",
                    'update' => "تعديل $label",
                    'delete' => "حذف $label",
                },
                'parent_id' => $parent->id
            ]);
            echo "  ✓ تم إنشاء الصلاحية الفرعية: $resourceName.$action\n";
        }
    }
    
    echo "\n";
}

echo "════════════════════════════════════════\n";
echo "✓ تم الانتهاء من تنظيم الصلاحيات بنجاح!\n";
echo "════════════════════════════════════════\n";
