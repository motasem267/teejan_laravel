-- إنشاء جدول أنواع الحصص
CREATE TABLE IF NOT EXISTS `lesson_types` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'اسم نوع الحصة',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_lesson_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول أنواع الحصص';

-- إدراج البيانات الأساسية
INSERT INTO `lesson_types` (`name`, `created_at`, `updated_at`) VALUES
('حصة محلية', NOW(), NOW()),
('حصة دولية', NOW(), NOW());

-- إضافة عمود lesson_type_id لجدول lesson_times
ALTER TABLE `lesson_times` 
ADD COLUMN `lesson_type_id` INT UNSIGNED NULL AFTER `period_number`,
ADD CONSTRAINT `fk_lesson_times_lesson_type` 
    FOREIGN KEY (`lesson_type_id`) 
    REFERENCES `lesson_types` (`id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE;

-- حذف عمود is_break
ALTER TABLE `lesson_times` DROP COLUMN `is_break`;

-- تحديث السجلات الموجودة (اختياري - يمكنك تعيين نوع افتراضي)
UPDATE `lesson_times` SET `lesson_type_id` = 1 WHERE `lesson_type_id` IS NULL;
