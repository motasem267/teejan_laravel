-- إضافة عمود section_id إلى جدول students
ALTER TABLE `students` 
ADD COLUMN `section_id` BIGINT UNSIGNED NULL AFTER `grade_id`;

-- إضافة foreign key constraint
ALTER TABLE `students`
ADD CONSTRAINT `students_section_id_foreign` 
FOREIGN KEY (`section_id`) 
REFERENCES `sections` (`id`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
