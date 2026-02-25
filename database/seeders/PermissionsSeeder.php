<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            ['name' => 'students', 'label' => 'الطلاب'],
            ['name' => 'student_enrollments', 'label' => 'قيد الطلبة', 'extra_actions' => [
                ['suffix' => '.update', 'label' => 'تحديث قيد طالب'],
            ]],
            ['name' => 'employees', 'label' => 'الموظفين'],
            ['name' => 'grades', 'label' => 'المراحل الدراسية'],
            ['name' => 'subjects', 'label' => 'المواد'],
            ['name' => 'marks', 'label' => 'الدرجات'],
            ['name' => 'classes', 'label' => 'الصفوف'],
            ['name' => 'sections', 'label' => 'الشعب'],
            ['name' => 'academic-years', 'label' => 'السنوات الدراسية'],
            ['name' => 'academic-periods', 'label' => 'الفترات الدراسية'],
            ['name' => 'teacher-classes', 'label' => 'توزيع المعلمين'],
            ['name' => 'evaluation_types', 'label' => 'أنواع التقييمات'],
            ['name' => 'evaluation-questions', 'label' => 'أسئلة التقييم'],
            ['name' => 'evaluation-answers', 'label' => 'إجابات التقييم'],
            ['name' => 'student-evaluation', 'label' => 'تقييم الطلاب'],
            ['name' => 'employee-types', 'label' => 'أنواع الموظفين'],
            ['name' => 'employee-statuses', 'label' => 'حالات الموظفين'],
            ['name' => 'salary-types', 'label' => 'أنواع الرواتب'],
            ['name' => 'permissions', 'label' => 'الصلاحيات'],
            ['name' => 'activity-logs', 'label' => 'سجل النشاطات'],
            ['name' => 'parents', 'label' => 'أولياء الأمور'],
            ['name' => 'expenses', 'label' => 'المصروفات'],
            ['name' => 'expenses_types', 'label' => 'أنواع المصروفات'],
            ['name' => 'installments', 'label' => 'الأقساط'],
            ['name' => 'installment_types', 'label' => 'أنواع الأقساط'],
            ['name' => 'annual_subscription_fees', 'label' => 'الاشتراكات السنوية'],
            ['name' => 'bonuses', 'label' => 'المكافآت'],
            ['name' => 'bonus_types', 'label' => 'أنواع العلاوات'],
            ['name' => 'deductions', 'label' => 'الخصومات'],
            ['name' => 'deduction_types', 'label' => 'أنواع الخصومات'],
            ['name' => 'days', 'label' => 'الأيام'],
            ['name' => 'lesson-times', 'label' => 'أوقات الحصص'],
            ['name' => 'lesson_types', 'label' => 'أنواع الحصص'],
            ['name' => 'school-schedules', 'label' => 'الجدول الدراسي'],
            ['name' => 'salaries', 'label' => 'المرتبات'],
            ['name' => 'work-days-calendars', 'label' => 'أيام الدوام'],
            ['name' => 'academic-period-grades', 'label' => 'اعلان النتائج'],
            ['name' => 'grade-promotion', 'label' => 'ترحيل الطلبة'],
            ['name' => 'student-evaluations-report', 'label' => 'تقرير تقييمات الطلاب', 'skip_default_actions' => true, 'extra_actions' => [
                ['suffix' => '.view', 'label' => 'عرض التقرير'],
                ['suffix' => '.export-pdf', 'label' => 'تصدير PDF'],
            ]],
            ['name' => 'week-results', 'label' => 'نتائج الأسابيع', 'extra_actions' => [
                ['suffix' => '.monitoring', 'label' => 'مراقبة التقييمات الأسبوعية'],
            ]],
            ['name' => 'assign-permissions', 'label' => 'إعطاء الصلاحيات'],
        ];

        $actions = [
            ['suffix' => '.view', 'label' => 'عرض'],
            ['suffix' => '.create', 'label' => 'إضافة'],
            ['suffix' => '.edit', 'label' => 'تعديل'],
            ['suffix' => '.delete', 'label' => 'حذف'],
        ];

        // Clear existing permissions (disable foreign key checks)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employee_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        
        foreach ($resources as $resource) {
            // Create parent permission
            $parentId = DB::table('permissions')->insertGetId([
                'name' => $resource['name'],
                'label' => $resource['label'],
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create action permissions (skip if skip_default_actions is true)
            if (!isset($resource['skip_default_actions']) || !$resource['skip_default_actions']) {
                foreach ($actions as $action) {
                    DB::table('permissions')->insert([
                        'name' => $resource['name'] . $action['suffix'],
                        'label' => $action['label'] . ' ' . $resource['label'],
                        'parent_id' => $parentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Create extra action permissions (if any)
            if (isset($resource['extra_actions']) && is_array($resource['extra_actions'])) {
                foreach ($resource['extra_actions'] as $extra) {
                    DB::table('permissions')->insert([
                        'name' => $resource['name'] . $extra['suffix'],
                        'label' => $extra['label'],
                        'parent_id' => $parentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
