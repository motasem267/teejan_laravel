<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "إصلاح الصلاحيات المكررة...\n\n";

// 1. معالجة salary-types
echo "1. معالجة أنواع الرواتب:\n";
$oldSalaryParent = Permission::where('name', 'salary-types')->first();
$newSalaryParent = Permission::where('name', 'salary_types')->whereNull('parent_id')->first();

if ($oldSalaryParent && $newSalaryParent) {
    // نقل الصلاحيات الفرعية من القديم إلى الجديد
    Permission::where('parent_id', $oldSalaryParent->id)->update(['parent_id' => $newSalaryParent->id]);
    echo "  ✓ تم نقل الصلاحيات الفرعية\n";
    
    // حذف الرئيسية القديمة
    $oldSalaryParent->delete();
    echo "  ✓ تم حذف الصلاحية الرئيسية القديمة\n";
} else {
    echo "  ○ لا توجد صلاحيات قديمة لأنواع الرواتب\n";
}

// 2. معالجة evaluation-types
echo "\n2. معالجة أنواع التقييمات:\n";
$oldEvalParent = Permission::where('name', 'evaluation-types')->first();
$newEvalParent = Permission::where('name', 'evaluation_types')->whereNull('parent_id')->first();

if (!$newEvalParent) {
    // إنشاء الصلاحية الرئيسية الجديدة
    $newEvalParent = Permission::create([
        'name' => 'evaluation_types',
        'label' => 'أنواع التقييمات',
        'parent_id' => null
    ]);
    echo "  ✓ تم إنشاء الصلاحية الرئيسية الجديدة\n";
}

if ($oldEvalParent && $newEvalParent && $oldEvalParent->id !== $newEvalParent->id) {
    // نقل الصلاحيات الفرعية
    Permission::where('parent_id', $oldEvalParent->id)->update(['parent_id' => $newEvalParent->id]);
    echo "  ✓ تم نقل الصلاحيات الفرعية\n";
    
    // حذف الرئيسية القديمة
    $oldEvalParent->delete();
    echo "  ✓ تم حذف الصلاحية الرئيسية القديمة\n";
} else {
    echo "  ○ لا توجد صلاحيات قديمة لأنواع التقييمات\n";
}

// 3. حذف الصلاحيات الفردية المكررة (186-195)
echo "\n3. حذف الصلاحيات الفردية المكررة:\n";
$duplicateIds = [186, 187, 188, 189, 190, 191, 192, 193, 194, 195];
$deleted = Permission::whereIn('id', $duplicateIds)->whereNull('parent_id')->delete();
echo "  ✓ تم حذف $deleted صلاحية مكررة\n";

echo "\n════════════════════════════════════════\n";
echo "✓ تم الانتهاء من إصلاح الصلاحيات!\n";
echo "════════════════════════════════════════\n";
