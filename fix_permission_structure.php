<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "════════════════════════════════════════════════════════════\n";
echo "إصلاح هيكل الصلاحيات:\n";
echo "════════════════════════════════════════════════════════════\n\n";

// التأكد من أن جميع الصلاحيات الأب موجودة وأن الصلاحيات الفرعية مرتبطة بها بشكل صحيح
$parentPermissions = [
    [
        'name' => 'attendance-report',
        'label' => 'تقرير الحضور',
        'child' => 'attendance-report.view',
        'child_label' => 'عرض تقرير الحضور'
    ],
    [
        'name' => 'expenses-report',
        'label' => 'تقرير المصاريف',
        'child' => 'expenses-report.view',
        'child_label' => 'عرض تقرير المصاريف'
    ],
    [
        'name' => 'salaries-report',
        'label' => 'تقرير الرواتب',
        'child' => 'salaries-report.view',
        'child_label' => 'عرض تقرير الرواتب'
    ],
    [
        'name' => 'revenue-report',
        'label' => 'تقرير الإيرادات',
        'child' => 'revenue-report.view',
        'child_label' => 'عرض تقرير الإيرادات'
    ]
];

foreach ($parentPermissions as $perms) {
    // التأكد من وجود الصلاحية الأب
    $parent = DB::table('permissions')->where('name', $perms['name'])->first();
    if (!$parent) {
        DB::table('permissions')->insert([
            'name' => $perms['name'],
            'label' => $perms['label'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $parent = DB::table('permissions')->where('name', $perms['name'])->first();
        echo "✓ تم إنشاء الصلاحية الأب: {$perms['name']}\n";
    }
    
    // التأكد من وجود الصلاحية الفرعية
    $child = DB::table('permissions')->where('name', $perms['child'])->first();
    if (!$child) {
        DB::table('permissions')->insert([
            'name' => $perms['child'],
            'label' => $perms['child_label'],
            'parent_id' => $parent->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✓ تم إنشاء الصلاحية الفرعية: {$perms['child']}\n";
    } else if ($child->parent_id !== $parent->id) {
        // إصلاح الصلاحية الفرعية إذا كان parent_id خاطئ
        DB::table('permissions')
            ->where('name', $perms['child'])
            ->update(['parent_id' => $parent->id]);
        echo "✓ تم إصلاح ارتباط الصلاحية الفرعية: {$perms['child']}\n";
    }
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "الصلاحيات النهائية:\n";
echo "════════════════════════════════════════════════════════════\n\n";

$allPerms = DB::table('permissions')
    ->whereIn('name', [
        'attendance-report',
        'attendance-report.view',
        'expenses-report',
        'expenses-report.view',
        'salaries-report',
        'salaries-report.view',
        'revenue-report',
        'revenue-report.view',
    ])
    ->orderBy('parent_id')
    ->orderBy('name')
    ->get(['id', 'name', 'label', 'parent_id']);

foreach ($allPerms as $p) {
    $type = $p->parent_id ? "├─ " : "▪ ";
    echo "{$type}{$p->name}\n";
    echo "   └─ {$p->label}\n\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "✓ تم إصلاح هيكل الصلاحيات بنجاح!\n";
echo "════════════════════════════════════════════════════════════\n";
