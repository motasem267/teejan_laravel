# دليل الصلاحيات - نظام الجدول الدراسي
## School Schedule Permissions Guide

---

## ✅ تم إضافة الصلاحيات بنجاح

تم إضافة صلاحيات نظام الجدول الدراسي في:
1. ✓ **PermissionsSeeder.php** - لإضافة الصلاحيات عبر Laravel
2. ✓ **school_timetable_tables.sql** - تم تحديثه لتضمين الصلاحيات
3. ✓ **school_timetable_permissions.sql** - ملف مخصص للصلاحيات فقط

---

## 📋 الصلاحيات المضافة

### الصلاحية الرئيسية (Parent Permission):
```
name: school-schedules
label: الجدول الدراسي
```

### الصلاحيات الفرعية (Child Permissions):
| الاسم البرمجي | الاسم بالعربية | الوصف |
|-------------|--------------|-------|
| `school-schedules.view` | عرض الجدول الدراسي | إمكانية عرض واستعراض الجداول |
| `school-schedules.create` | إضافة الجدول الدراسي | إمكانية إنشاء جداول جديدة |
| `school-schedules.edit` | تعديل الجدول الدراسي | إمكانية تعديل الجداول الموجودة |
| `school-schedules.delete` | حذف الجدول الدراسي | إمكانية حذف الجداول |

---

## 🚀 طرق إضافة الصلاحيات

### الطريقة الأولى: باستخدام Laravel Seeder (موصى بها) ⭐

```bash
# تشغيل Seeder الصلاحيات
php artisan db:seed --class=PermissionsSeeder

# أو تشغيل جميع الـ Seeders
php artisan db:seed
```

**مميزات:**
- ✅ سريعة وآمنة
- ✅ تضمن إضافة جميع الصلاحيات بشكل منظم
- ✅ تحديث تلقائي في حالة إعادة التشغيل
- ⚠️ تحذير: ستحذف الصلاحيات الموجودة وتضيفها من جديد

---

### الطريقة الثانية: باستخدام SQL مباشرة

#### أ. تشغيل ملف SQL الكامل:
```bash
mysql -u root -p database_name < school_timetable_tables.sql
```

#### ب. تشغيل ملف الصلاحيات فقط:
```bash
mysql -u root -p database_name < school_timetable_permissions.sql
```

#### ج. من phpMyAdmin:
1. افتح phpMyAdmin
2. اختر قاعدة البيانات
3. اذهب إلى تبويب "SQL"
4. انسخ محتوى ملف `school_timetable_permissions.sql`
5. اضغط على "تنفيذ" أو "Go"

---

## 👥 منح الصلاحيات للموظفين

### الطريقة الأولى: من خلال Filament Admin Panel (موصى بها) ⭐

1. **سجل دخول كمسؤول** في Filament Panel
2. **انتقل إلى قسم الموظفين** (Employees)
3. **اختر الموظف** الذي تريد منحه الصلاحيات
4. **اذهب إلى تبويب الصلاحيات** (Permissions)
5. **حدد صلاحيات الجدول الدراسي:**
   - ✓ عرض الجدول الدراسي
   - ✓ إضافة الجدول الدراسي
   - ✓ تعديل الجدول الدراسي
   - ✓ حذف الجدول الدراسي
6. **احفظ التغييرات**

---

### الطريقة الثانية: باستخدام SQL

#### منح جميع صلاحيات الجدول الدراسي لموظف معين:
```sql
-- مثال: منح الصلاحيات للموظف رقم 1
INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT 1, id FROM `permissions` 
WHERE `name` IN (
    'school-schedules',
    'school-schedules.view',
    'school-schedules.create',
    'school-schedules.edit',
    'school-schedules.delete'
);
```

#### منح صلاحية محددة فقط:
```sql
-- مثال: منح صلاحية العرض فقط للموظف رقم 2
INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT 2, id FROM `permissions` 
WHERE `name` = 'school-schedules.view';
```

---

## 🔍 استعلامات التحقق والفحص

### 1. عرض جميع صلاحيات الجدول الدراسي:
```sql
SELECT 
    p.id AS 'المعرف',
    p.name AS 'الاسم البرمجي',
    p.label AS 'الاسم بالعربية',
    CASE 
        WHEN p.parent_id IS NULL THEN 'رئيسية' 
        ELSE 'فرعية' 
    END AS 'النوع'
FROM permissions p
WHERE p.name LIKE 'school-schedules%'
ORDER BY p.parent_id, p.id;
```

**النتيجة المتوقعة:**
```
+--------+---------------------------+------------------------+---------+
| المعرف | الاسم البرمجي              | الاسم بالعربية          | النوع   |
+--------+---------------------------+------------------------+---------+
| 101    | school-schedules          | الجدول الدراسي          | رئيسية  |
| 102    | school-schedules.view     | عرض الجدول الدراسي      | فرعية   |
| 103    | school-schedules.create   | إضافة الجدول الدراسي    | فرعية   |
| 104    | school-schedules.edit     | تعديل الجدول الدراسي    | فرعية   |
| 105    | school-schedules.delete   | حذف الجدول الدراسي      | فرعية   |
+--------+---------------------------+------------------------+---------+
```

---

### 2. عرض الموظفين الذين لديهم صلاحية الجدول الدراسي:
```sql
SELECT 
    e.id AS 'معرف الموظف',
    e.name AS 'اسم الموظف',
    p.label AS 'الصلاحية'
FROM employees e
JOIN employee_permissions ep ON e.id = ep.employee_id
JOIN permissions p ON ep.permission_id = p.id
WHERE p.name LIKE 'school-schedules%'
ORDER BY e.id, p.id;
```

---

### 3. التحقق من صلاحيات موظف معين:
```sql
SELECT 
    p.name AS 'الاسم البرمجي',
    p.label AS 'الاسم بالعربية'
FROM employee_permissions ep
JOIN permissions p ON ep.permission_id = p.id
WHERE ep.employee_id = 1  -- ضع معرف الموظف هنا
  AND p.name LIKE 'school-schedules%';
```

---

## 🗑️ حذف الصلاحيات (في حالة الحاجة)

### حذف صلاحيات الجدول الدراسي فقط:
```sql
-- حذف ربط الصلاحيات مع الموظفين أولاً
DELETE FROM `employee_permissions` 
WHERE `permission_id` IN (
    SELECT id FROM `permissions` WHERE `name` LIKE 'school-schedules%'
);

-- ثم حذف الصلاحيات نفسها
DELETE FROM `permissions` WHERE `name` LIKE 'school-schedules%';
```

---

## 🔐 كيفية التحقق من الصلاحيات في الكود

### في SchoolScheduleResource.php:
```php
protected static function getResourcePermissionName(): string
{
    return 'school-schedules';
}
```

هذا يضمن أن Filament سيتحقق تلقائياً من الصلاحيات التالية:
- `school-schedules.view` - للوصول إلى صفحة القائمة
- `school-schedules.create` - للوصول إلى صفحة الإنشاء
- `school-schedules.edit` - للوصول إلى صفحة التعديل
- `school-schedules.delete` - لحذف السجلات

---

## 📊 سيناريوهات شائعة

### 1️⃣ منح صلاحية العرض فقط (للمشرفين):
```sql
-- المشرف يمكنه فقط عرض الجداول
INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT [employee_id], id FROM `permissions` 
WHERE `name` IN ('school-schedules', 'school-schedules.view');
```

### 2️⃣ منح صلاحيات العرض والإضافة (للمدخلين):
```sql
-- المدخل يمكنه العرض والإضافة
INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT [employee_id], id FROM `permissions` 
WHERE `name` IN (
    'school-schedules',
    'school-schedules.view',
    'school-schedules.create'
);
```

### 3️⃣ منح جميع الصلاحيات (للمدراء):
```sql
-- المدير لديه صلاحيات كاملة
INSERT INTO `employee_permissions` (`employee_id`, `permission_id`)
SELECT [employee_id], id FROM `permissions` 
WHERE `name` LIKE 'school-schedules%';
```

---

## ⚠️ ملاحظات مهمة

### 1. ترتيب إضافة الصلاحيات:
```
1️⃣ أولاً: قم بإنشاء الصلاحيات (عبر Seeder أو SQL)
2️⃣ ثانياً: قم بمنح الصلاحيات للموظفين المناسبين
3️⃣ ثالثاً: اختبر الوصول من خلال الموظفين
```

### 2. إذا لم تظهر الصلاحيات في Panel:
- تأكد من تشغيل Seeder أو SQL بنجاح
- تحقق من جدول `permissions` في قاعدة البيانات
- امسح الـ Cache: `php artisan cache:clear`
- أعد تسجيل الدخول

### 3. بنية الصلاحيات:
```
permissions (جدول الصلاحيات)
├── id
├── name (الاسم البرمجي)
├── label (الاسم المعروض)
├── parent_id (معرف الصلاحية الأب)
├── created_at
└── updated_at

employee_permissions (جدول ربط الموظفين بالصلاحيات)
├── employee_id
└── permission_id
```

---

## 🎯 قائمة تحقق

- [ ] تم إضافة الصلاحيات في قاعدة البيانات
- [ ] تم التحقق من وجود الصلاحيات باستخدام SQL
- [ ] تم منح الصلاحيات لموظف تجريبي
- [ ] تم اختبار الوصول من خلال الموظف
- [ ] تم التحقق من عمل التحقق من الصلاحيات في Panel

---

## 📞 استكشاف الأخطاء

### المشكلة: لا تظهر صفحة الجدول الدراسي في القائمة
**الحل:**
```sql
-- تحقق من أن الموظف لديه صلاحية school-schedules.view
SELECT * FROM employee_permissions ep
JOIN permissions p ON ep.permission_id = p.id
WHERE ep.employee_id = [employee_id]
  AND p.name = 'school-schedules.view';
```

### المشكلة: تظهر رسالة "غير مصرح"
**الحل:**
- تأكد من أن الموظف لديه الصلاحية المطلوبة للعملية
- امسح الـ Cache وأعد تسجيل الدخول

---

## ✅ اكتمل!

تم إعداد نظام الصلاحيات الكامل لنظام الجدول الدراسي. 🎉

**الآن يمكنك:**
- ✓ التحكم في من يمكنه الوصول إلى الجدول الدراسي
- ✓ تحديد صلاحيات مختلفة لموظفين مختلفين
- ✓ ضمان أمان البيانات والتحكم في الوصول
