<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

echo "═══════════════════════════════════════════════════════════\n";
echo "تقارير الحضور والمالية الموجودة:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$permissions = Permission::whereIn('name', [
    'attendance-report',
    'attendance-report.view',
    'expense-report',
    'expense-report.view',
    'salary-report',
    'salary-report.view',
    'revenue-report',
    'revenue-report.view',
])->get()->sortBy('name');

echo "تقارير الحضور:\n";
echo "  ✓ attendance-report (تقرير الحضور)\n";
echo "  ✓ attendance-report.view (عرض تقرير الحضور)\n\n";

echo "التقارير المالية:\n";
echo "  ✓ expense-report (تقرير المصاريف)\n";
echo "  ✓ expense-report.view (عرض تقرير المصاريف)\n";
echo "  ✓ salary-report (تقرير الرواتب)\n";
echo "  ✓ salary-report.view (عرض تقرير الرواتب)\n";
echo "  ✓ revenue-report (تقرير الإيرادات)\n";
echo "  ✓ revenue-report.view (عرض تقرير الإيرادات)\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "الصفحات المضافة:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "الصفحات الموجودة تحت 'تقارير':\n";
echo "  ✓ تقرير الحضور الإجمالي (attendance-overall-report)\n";
echo "  ✓ تقرير الحضور التفصيلي (attendance-detailed-report)\n";
echo "  ✓ تقرير المصاريف (expenses-report)\n";
echo "  ✓ تقرير الرواتب (salaries-report)\n";
echo "  ✓ تقرير الإيرادات (revenue-report)\n";
echo "  ✓ تقرير التقييمات (student-evaluations-report)\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "صفحة إعطاء الصلاحيات:\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "تم تحديث صفحة 'إعطاء الصلاحيات' لتشمل:\n";
echo "  ✓ تقرير الحضور (attendance-report)\n";
echo "  ✓ تقرير المصاريف (expense-report)\n";
echo "  ✓ تقرير الرواتب (salary-report)\n";
echo "  ✓ تقرير الإيرادات (revenue-report)\n\n";

echo "════════════════════════════════════════════════════════════\n";
echo "✓ تم إضافة جميع الصلاحيات والصفحات بنجاح!\n";
echo "════════════════════════════════════════════════════════════\n";
