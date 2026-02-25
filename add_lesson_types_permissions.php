<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

$resource = 'lesson_types';
$label = 'أنواع الحصص';
$actions = ['view-any', 'view', 'create', 'update', 'delete'];

// إنشاء الصلاحية الرئيسية
$parent = Permission::firstOrCreate(
    ['name' => $resource],
    ['label' => $label, 'parent_id' => null]
);

foreach ($actions as $action) {
    $permissionName = "$resource.$action";
    Permission::firstOrCreate(
        ['name' => $permissionName],
        ['label' => match($action) {
            'view-any' => "عرض $label",
            'view' => "عرض $label",
            'create' => "إضافة $label",
            'update' => "تعديل $label",
            'delete' => "حذف $label",
        }, 'parent_id' => $parent->id]
    );
}

echo "✓ تم إضافة صلاحيات أنواع الحصص بنجاح!\n";
