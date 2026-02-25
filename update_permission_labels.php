<?php

use Illuminate\Support\Facades\DB;

// تحديث تسميات صلاحية "اعلان النتائج"
$updated = DB::table('permissions')
    ->where('name', 'academic-period-grades')
    ->whereNull('parent_id')
    ->update([
        'label' => 'اعلان النتائج',
        'updated_at' => now()
    ]);

if ($updated) {
    echo "✅ تم تحديث اسم الصلاحية الرئيسية\n";
    
    // تحديث الصلاحيات الفرعية
    $parentId = DB::table('permissions')
        ->where('name', 'academic-period-grades')
        ->value('id');
    
    if ($parentId) {
        DB::table('permissions')
            ->where('name', 'academic-period-grades.view')
            ->update(['label' => 'عرض اعلان النتائج', 'updated_at' => now()]);
            
        DB::table('permissions')
            ->where('name', 'academic-period-grades.create')
            ->update(['label' => 'إضافة اعلان النتائج', 'updated_at' => now()]);
            
        DB::table('permissions')
            ->where('name', 'academic-period-grades.edit')
            ->update(['label' => 'تعديل اعلان النتائج', 'updated_at' => now()]);
            
        DB::table('permissions')
            ->where('name', 'academic-period-grades.delete')
            ->update(['label' => 'حذف اعلان النتائج', 'updated_at' => now()]);
        
        echo "✅ تم تحديث جميع الصلاحيات الفرعية (عرض، إضافة، تعديل، حذف)\n";
    }
    
    echo "✓ التحديث مكتمل - الآن سيظهر 'اعلان النتائج' في صفحة إعطاء الصلاحيات\n";
} else {
    echo "❌ لم يتم العثور على الصلاحية\n";
}
