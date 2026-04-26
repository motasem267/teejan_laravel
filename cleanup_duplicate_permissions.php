<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "════════════════════════════════════════════════════════════\n";
echo "حذف الصلاحيات المكررة:\n";
echo "════════════════════════════════════════════════════════════\n\n";

// حذف الصلاحيات المكررة
$toDelete = [
    'expense-report',
    'expense-report.view',
    'salary-report',
    'salary-report.view',
    'revenue-report.view', // نترك revenue-report الأب
];

$deleted = 0;
foreach ($toDelete as $permName) {
    $perm = DB::table('permissions')->where('name', $permName)->first();
    if ($perm) {
        // حذف الصلاحيات الفرعية أولاً إذا كانت موجودة
        DB::table('permissions')->where('parent_id', $perm->id)->delete();
        // ثم حذف الصلاحية نفسها
        DB::table('permissions')->where('name', $permName)->delete();
        // حذف من جدول الصلاحيات الزائفة
        DB::table('employee_permissions')->where('permission_id', $perm->id)->delete();
        echo "✓ تم حذف: $permName\n";
        $deleted++;
    }
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "✓ تم حذف $deleted صلاحية مكررة\n";
echo "════════════════════════════════════════════════════════════\n\n";

// عرض الصلاحيات المتبقية
echo "الصلاحيات المتبقية للتقارير:\n\n";

$remaining = DB::table('permissions')
    ->whereIn('name', [
        'expenses-report.view',
        'salaries-report.view',
        'revenue-report.view',
        'revenue-report',
        'attendance-report',
        'attendance-report.view',
    ])
    ->orderBy('name')
    ->get(['id', 'name', 'label', 'parent_id']);

foreach ($remaining as $p) {
    $parent_info = $p->parent_id ? " (Parent ID: {$p->parent_id})" : " (Parent Permission)";
    echo "  • {$p->name} - {$p->label}{$parent_info}\n";
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "✓ تم إصلاح الصلاحيات بنجاح!\n";
echo "════════════════════════════════════════════════════════════\n";
