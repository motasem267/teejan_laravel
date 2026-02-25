-- ======================================
-- نظام الجدول الدراسي - SQL Script
-- School Timetable System - SQL Script
-- ======================================

-- إنشاء جدول الأيام
-- Create Days Table
CREATE TABLE IF NOT EXISTS `days` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `day_name_ar` VARCHAR(50) NOT NULL COMMENT 'اسم اليوم بالعربية',
    `day_order` INT NOT NULL COMMENT 'ترتيب اليوم (1-7)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `day_order` (`day_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الأيام';

-- إنشاء جدول أوقات الحصص
-- Create Lesson Times Table
CREATE TABLE IF NOT EXISTS `lesson_times` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `period_number` INT NULL COMMENT 'رقم الحصة (null للاستراحة)',
    `start_time` TIME NOT NULL COMMENT 'وقت البداية',
    `end_time` TIME NOT NULL COMMENT 'وقت النهاية',
    `is_break` BOOLEAN DEFAULT FALSE COMMENT 'هل هي استراحة؟',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول أوقات الحصص';

-- إنشاء جدول الجدول الدراسي
-- Create School Schedules Table
CREATE TABLE IF NOT EXISTS `school_schedules` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `teacher_class_id` INT UNSIGNED NOT NULL COMMENT 'معرّف العلاقة بين المعلم والصف',
    `day_id` INT UNSIGNED NOT NULL COMMENT 'معرّف اليوم',
    `lesson_time_id` INT UNSIGNED NOT NULL COMMENT 'معرّف وقت الحصة',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_schedule` (`teacher_class_id`, `day_id`, `lesson_time_id`),
    KEY `idx_day_lesson` (`day_id`, `lesson_time_id`),
    KEY `idx_teacher_class` (`teacher_class_id`),
    CONSTRAINT `fk_school_schedules_teacher_class` 
        FOREIGN KEY (`teacher_class_id`) 
        REFERENCES `teacher_classes` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_school_schedules_day` 
        FOREIGN KEY (`day_id`) 
        REFERENCES `days` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_school_schedules_lesson_time` 
        FOREIGN KEY (`lesson_time_id`) 
        REFERENCES `lesson_times` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الجدول الدراسي';

-- ======================================
-- إدراج البيانات الأساسية
-- Insert Base Data
-- ======================================

-- إدراج أيام الأسبوع
-- Insert Days of the Week
INSERT INTO `days` (`day_name_ar`, `day_order`) VALUES
('السبت', 1),
('الأحد', 2),
('الاثنين', 3),
('الثلاثاء', 4),
('الأربعاء', 5),
('الخميس', 6);

-- إدراج أوقات الحصص (مثال)
-- Insert Lesson Times (Example)
INSERT INTO `lesson_times` (`period_number`, `start_time`, `end_time`, `is_break`) VALUES
-- الحصة الأولى
(1, '08:00:00', '08:45:00', 0),
-- الحصة الثانية
(2, '08:45:00', '09:30:00', 0),
-- استراحة
(NULL, '09:30:00', '09:45:00', 1),
-- الحصة الثالثة
(3, '09:45:00', '10:30:00', 0),
-- الحصة الرابعة
(4, '10:30:00', '11:15:00', 0),
-- استراحة
(NULL, '11:15:00', '11:30:00', 1),
-- الحصة الخامسة
(5, '11:30:00', '12:15:00', 0),
-- الحصة السادسة
(6, '12:15:00', '13:00:00', 0),
-- استراحة الغداء
(NULL, '13:00:00', '13:30:00', 1),
-- الحصة السابعة
(7, '13:30:00', '14:15:00', 0);

-- ======================================
-- إضافة صلاحيات الجدول الدراسي
-- Add School Schedule Permissions
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
-- استعلامات مفيدة
-- Useful Queries
-- ======================================

-- عرض جميع الأيام مرتبة
-- Show all days ordered
-- SELECT * FROM days ORDER BY day_order;

-- عرض جميع أوقات الحصص مرتبة
-- Show all lesson times ordered
-- SELECT * FROM lesson_times ORDER BY period_number;

-- عرض الجدول الدراسي الكامل مع جميع التفاصيل
-- Show complete schedule with all details
/*
SELECT 
    d.day_name_ar AS 'اليوم',
    CASE 
        WHEN lt.is_break = 1 THEN 'استراحة'
        ELSE CONCAT('الحصة ', lt.period_number)
    END AS 'الحصة',
    lt.start_time AS 'من',
    lt.end_time AS 'إلى',
    e.name AS 'المعلم',
    s.name AS 'المادة',
    g.name AS 'الصف',
    sec.name AS 'القسم'
FROM school_schedules ss
JOIN days d ON ss.day_id = d.id
JOIN lesson_times lt ON ss.lesson_time_id = lt.id
JOIN teacher_classes tc ON ss.teacher_class_id = tc.id
JOIN employees e ON tc.teacher_id = e.id
JOIN subjects s ON tc.subject_id = s.id
JOIN classes c ON tc.class_id = c.id
JOIN grades g ON c.grade_id = g.id
JOIN sections sec ON c.section_id = sec.id
ORDER BY d.day_order, lt.period_number;
*/

-- البحث عن جميع حصص معلم معين في يوم معين
-- Find all lessons for a specific teacher on a specific day
/*
SELECT 
    d.day_name_ar AS 'اليوم',
    lt.period_number AS 'رقم الحصة',
    lt.start_time AS 'من',
    lt.end_time AS 'إلى',
    s.name AS 'المادة',
    g.name AS 'الصف',
    sec.name AS 'القسم'
FROM school_schedules ss
JOIN days d ON ss.day_id = d.id
JOIN lesson_times lt ON ss.lesson_time_id = lt.id
JOIN teacher_classes tc ON ss.teacher_class_id = tc.id
JOIN employees e ON tc.teacher_id = e.id
JOIN subjects s ON tc.subject_id = s.id
JOIN classes c ON tc.class_id = c.id
JOIN grades g ON c.grade_id = g.id
JOIN sections sec ON c.section_id = sec.id
WHERE e.id = 1  -- ضع معرّف المعلم هنا
  AND d.id = 1  -- ضع معرّف اليوم هنا
ORDER BY lt.period_number;
*/

-- البحث عن جميع حصص فصل معين في يوم معين
-- Find all lessons for a specific class on a specific day
/*
SELECT 
    d.day_name_ar AS 'اليوم',
    lt.period_number AS 'رقم الحصة',
    lt.start_time AS 'من',
    lt.end_time AS 'إلى',
    e.name AS 'المعلم',
    s.name AS 'المادة'
FROM school_schedules ss
JOIN days d ON ss.day_id = d.id
JOIN lesson_times lt ON ss.lesson_time_id = lt.id
JOIN teacher_classes tc ON ss.teacher_class_id = tc.id
JOIN employees e ON tc.teacher_id = e.id
JOIN subjects s ON tc.subject_id = s.id
JOIN classes c ON tc.class_id = c.id
WHERE c.id = 1  -- ضع معرّف الفصل هنا
  AND d.id = 1  -- ضع معرّف اليوم هنا
ORDER BY lt.period_number;
*/

-- ======================================
-- ملاحظات مهمة
-- Important Notes
-- ======================================

/*
1. تأكد من وجود جدول teacher_classes قبل تشغيل هذا السكريبت
   Make sure teacher_classes table exists before running this script

2. يمكنك تعديل أوقات الحصص حسب احتياجاتك
   You can modify lesson times according to your needs

3. استخدم UNIQUE KEY للتأكد من عدم تكرار الجداول
   UNIQUE KEY is used to prevent duplicate schedules

4. Foreign Keys مع ON DELETE CASCADE تضمن حذف الجداول المرتبطة تلقائياً
   Foreign Keys with ON DELETE CASCADE ensure automatic deletion of related schedules

5. يمكنك إضافة يوم الجمعة إذا لزم الأمر
   You can add Friday if needed
*/
