<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════\n";
echo "فحص الصلاحيات المتعلقة بالتقارير:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$reports = DB::table('permissions')
    ->whereIn('name', [
        'expenses-report.view',
        'expense-report.view',
        'salaries-report.view',
        'salary-report.view',
        'revenue-report.view',
        'attendance-report.view',
        'expenses-report',
        'expense-report',
        'salaries-report',
        'salary-report',
        'revenue-report',
        'attendance-report'
    ])
    ->orderBy('name')
    ->get(['id', 'name', 'label', 'parent_id']);

echo "الصلاحيات الموجودة:\n";
foreach ($reports as $p) {
    $parent_id = $p->parent_id ? " (Parent ID: {$p->parent_id})" : " (Parent Permission)";
    echo "  • {$p->name} - {$p->label}{$parent_id}\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "المشاكل المكتشفة:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// تحقق من التكرارات
$allReportNames = $reports->pluck('name')->toArray();
$duplicates = array_filter(array_count_values($allReportNames), fn($count) => $count > 1);

if (!empty($duplicates)) {
    echo "❌ صلاحيات مكررة:\n";
    foreach (array_keys($duplicates) as $dup) {
        echo "  • $dup\n";
    }
} else {
    echo "✓ لا توجد صلاحيات مكررة\n";
}

echo "\n";

// تحقق من الصلاحيات التي بدون اب
$orphans = $reports->filter(fn($p) => is_null($p->parent_id))->pluck('name')->toArray();
if (!empty($orphans)) {
    echo "⚠️ صلاحيات بدون أب (يجب أن تكون لها صلاحيات فرعية):\n";
    foreach ($orphans as $orphan) {
        $children = DB::table('permissions')->where('parent_id', DB::table('permissions')->where('name', $orphan)->value('id'))->pluck('name')->toArray();
        if (empty($children)) {
            echo "  • $orphan - بدون صلاحيات فرعية ❌\n";
        } else {
            echo "  • $orphan - صلاحيات فرعية: " . implode(', ', $children) . "\n";
        }
    }
} else {
    echo "✓ جميع الصلاحيات لها أب أو هي صلاحيات أب\n";
}

echo "\n════════════════════════════════════════════════════════════\n";
