-- ======================================
-- إضافة صلاحيات الجدول الدراسي
-- Add School Schedule Permissions
-- ======================================
-- تاريخ الإنشاء: 2026-01-20
-- Created: 2026-01-20

-- ملاحظة: يمكن تشغيل هذا الملف منفصلاً لإضافة الصلاحيات فقط
-- Note: This file can be run separately to add permissions only

-- ======================================
-- الطريقة الأولى: استخدام SQL مباشرة
-- Method 1: Using Direct SQL
-- ======================================

-- إضافة الصلاحية الرئيسية للأيام
-- Add main permission for days
INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) 
VALUES ('days', 'الأيام', NULL, NOW(), NOW());

SET @days_parent_id = LAST_INSERT_ID();

INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) VALUES
('days.view', 'عرض الأيام', @days_parent_id, NOW(), NOW()),
('days.create', 'إضافة الأيام', @days_parent_id, NOW(), NOW()),
('days.edit', 'تعديل الأيام', @days_parent_id, NOW(), NOW()),
('days.delete', 'حذف الأيام', @days_parent_id, NOW(), NOW());

-- إضافة الصلاحية الرئيسية لأوقات الحصص
-- Add main permission for lesson times
INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) 
VALUES ('lesson-times', 'أوقات الحصص', NULL, NOW(), NOW());

SET @lesson_times_parent_id = LAST_INSERT_ID();

INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) VALUES
('lesson-times.view', 'عرض أوقات الحصص', @lesson_times_parent_id, NOW(), NOW()),
('lesson-times.create', 'إضافة أوقات الحصص', @lesson_times_parent_id, NOW(), NOW()),
('lesson-times.edit', 'تعديل أوقات الحصص', @lesson_times_parent_id, NOW(), NOW()),
('lesson-times.delete', 'حذف أوقات الحصص', @lesson_times_parent_id, NOW(), NOW());

-- إضافة الصلاحية الرئيسية للجدول الدراسي
-- Add main permission for school schedules
INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) 
VALUES ('school-schedules', 'الجدول الدراسي', NULL, NOW(), NOW());

-- الحصول على معرف الصلاحية الرئيسية
-- Get the parent permission ID
SET @parent_id = LAST_INSERT_ID();

-- إضافة صلاحيات العمليات (عرض، إضافة، تعديل، حذف)
-- Add action permissions (view, create, edit, delete)
INSERT INTO `permissions` (`name`, `label`, `parent_id`, `created_at`, `updated_at`) VALUES
('school-schedules.view', 'عرض الجدول الدراسي', @parent_id, NOW(), NOW()),
('school-schedules.create', 'إضافة الجدول الدراسي', @parent_id, NOW(), NOW()),
('school-schedules.edit', 'تعديل الجدول الدراسي', @parent_id, NOW(), NOW()),
('school-schedules.delete', 'حذف الجدول الدراسي', @parent_id, NOW(), NOW());

-- ======================================
-- الطريقة الثانية: باستخدام Laravel Seeder
-- Method 2: Using Laravel Seeder
-- ======================================

/*
لتشغيل الـ Seeder:
To run the seeder:

php artisan db:seed --class=PermissionsSeeder

أو إعادة تشغيل جميع الـ Seeders:
Or re-run all seeders:

php artisan db:seed

ملاحظة: سيقوم الـ Seeder بحذف جميع الصلاحيات الموجودة وإضافتها من جديد
Note: The seeder will truncate all existing permissions and add them fresh
*/

-- ======================================
-- منح الصلاحيات لموظف معين
-- Grant Permissions to Specific Employee
-- ======================================

/*
-- مثال: منح جميع صلاحيات الجدول الدراسي للموظف رقم 1
-- Example: Grant all school schedule permissions to employee ID 1

INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT 1, id FROM `permissions` 
WHERE `name` IN (
    'school-schedules',
    'school-schedules.view',
    'school-schedules.create',
    'school-schedules.edit',
    'school-schedules.delete'
);
*/

-- ======================================
-- استعلامات مفيدة للتحقق
-- Useful Queries for Verification
-- ======================================

-- عرض جميع صلاحيات الجدول الدراسي
-- Show all school schedule permissions
/*
SELECT 
    p.id,
    p.name,
    p.label,
    p.parent_id,
    CASE WHEN p.parent_id IS NULL THEN 'رئيسية' ELSE 'فرعية' END AS 'النوع'
FROM permissions p
WHERE p.name LIKE 'school-schedules%'
ORDER BY p.parent_id, p.id;
*/

-- عرض الموظفين الذين لديهم صلاحية الجدول الدراسي
-- Show employees who have school schedule permissions
/*
SELECT 
    e.id AS 'معرف الموظف',
    e.name AS 'اسم الموظف',
    p.name AS 'اسم الصلاحية',
    p.label AS 'وصف الصلاحية'
FROM employees e
JOIN employee_permissions ep ON e.id = ep.employee_id
JOIN permissions p ON ep.permission_id = p.id
WHERE p.name LIKE 'school-schedules%'
ORDER BY e.id, p.id;
*/

-- حذف صلاحيات الجدول الدراسي (في حالة الحاجة لإعادة الإضافة)
-- Delete school schedule permissions (if need to re-add)
/*
DELETE FROM `employee_permissions` 
WHERE `permission_id` IN (
    SELECT id FROM `permissions` WHERE `name` LIKE 'school-schedules%'
);

DELETE FROM `permissions` WHERE `name` LIKE 'school-schedules%';
*/

-- ======================================
-- ملاحظات مهمة
-- Important Notes
-- ======================================

/*
1. الصلاحيات المضافة:
   Added Permissions:
   
   - school-schedules (رئيسية - Parent)
   - school-schedules.view (عرض - View)
   - school-schedules.create (إضافة - Create)
   - school-schedules.edit (تعديل - Edit)
   - school-schedules.delete (حذف - Delete)

2. بنية الصلاحيات:
   Permissions Structure:
   
   - parent_id = NULL للصلاحية الرئيسية
   - parent_id = ID الصلاحية الرئيسية للصلاحيات الفرعية

3. كيفية منح الصلاحيات:
   How to Grant Permissions:
   
   أ. من خلال Filament Admin Panel:
      Through Filament Admin Panel:
      - انتقل إلى الموظفين
      - اختر الموظف
      - اذهب إلى تبويب الصلاحيات
      - حدد صلاحيات الجدول الدراسي
   
   ب. من خلال SQL:
      Through SQL:
      - استخدم الاستعلامات الموجودة أعلاه

4. التحقق من الصلاحيات في الكود:
   Permission Check in Code:
   
   في SchoolScheduleResource.php:
   In SchoolScheduleResource.php:
   
   protected static function getResourcePermissionName(): string
   {
       return 'school-schedules';
   }

5. إذا قمت بتشغيل PermissionsSeeder:
   If you run PermissionsSeeder:
   
   - سيتم إضافة صلاحيات الجدول الدراسي تلقائياً
   - لا حاجة لتشغيل هذا الملف
   - The school schedule permissions will be added automatically
   - No need to run this file
*/
