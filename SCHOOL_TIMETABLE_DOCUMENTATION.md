# نظام الجدول الدراسي - School Timetable System

## نظرة عامة
نظام متكامل لإدارة الجدول الدراسي في Laravel Filament يتضمن منطق متقدم للتحقق من التضاربات.

---

## 📁 الملفات المنشأة

### Models
```
app/Models/
├── Day.php                 # نموذج الأيام
├── LessonTime.php          # نموذج أوقات الحصص
└── SchoolSchedule.php      # نموذج الجدول الدراسي
```

### Filament Resources
```
app/Filament/Resources/SchoolSchedules/
├── SchoolScheduleResource.php     # المورد الرئيسي
├── Schemas/
│   └── SchoolScheduleForm.php      # نموذج الإدخال
├── Tables/
│   └── SchoolScheduleTable.php     # جدول العرض
└── Pages/
    ├── ListSchoolSchedules.php      # صفحة القائمة
    ├── CreateSchoolSchedule.php     # صفحة الإضافة
    └── EditSchoolSchedule.php       # صفحة التعديل
```

### Validation Rules
```
app/Rules/
├── NoTeacherConflict.php   # قاعدة منع تضارب المعلم
└── NoClassConflict.php     # قاعدة منع تضارب الفصل
```

### Controllers & Validators
```
app/Http/Controllers/Filament/SchoolSchedules/
└── SchoolScheduleValidator.php  # منطق التحقق من التضاربات
```

---

## 🗄️ بنية قاعدة البيانات المتوقعة

### جدول days
```sql
CREATE TABLE days (
    id INT PRIMARY KEY AUTO_INCREMENT,
    day_name_ar VARCHAR(50) NOT NULL,  -- السبت، الأحد، إلخ
    day_order INT NOT NULL             -- 1, 2, 3...
);
```

### جدول lesson_times
```sql
CREATE TABLE lesson_times (
    id INT PRIMARY KEY AUTO_INCREMENT,
    period_number INT NOT NULL,        -- 1, 2, 3...
    start_time TIME NOT NULL,          -- 08:00:00
    end_time TIME NOT NULL,            -- 08:45:00
    is_break BOOLEAN DEFAULT FALSE     -- هل هي استراحة؟
);
```

### جدول school_schedules
```sql
CREATE TABLE school_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_class_id INT NOT NULL,    -- الرابط مع teacher_classes
    day_id INT NOT NULL,               -- الرابط مع days
    lesson_time_id INT NOT NULL,       -- الرابط مع lesson_times
    FOREIGN KEY (teacher_class_id) REFERENCES teacher_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (day_id) REFERENCES days(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_time_id) REFERENCES lesson_times(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (teacher_class_id, day_id, lesson_time_id)
);
```

---

## 🔄 العلاقات بين الموديلات

### Day Model
- `HasMany` schoolSchedules

### LessonTime Model
- `HasMany` schoolSchedules

### SchoolSchedule Model
```php
- BelongsTo teacherClass
- BelongsTo day
- BelongsTo lessonTime

// Accessor Methods للوصول السريع:
- teacher()         // من خلال teacherClass
- subject()         // من خلال teacherClass
- classModel()      // من خلال teacherClass
```

### TeacherClass Model (محدث)
```php
// بالإضافة للعلاقات الموجودة:
- HasMany schoolSchedules
```

---

## 📋 منطق التحقق (Validation Logic)

### تضارب المعلم (Teacher Conflict)
```
✓ التحقق: لا يمكن لنفس المعلم أن يكون له حصتين في نفس اليوم والوقت
✓ المصدر: NoTeacherConflict Rule
✓ الفحص يتم في:
  - CreateSchoolSchedule::mutateFormDataBeforeCreate()
  - EditSchoolSchedule::mutateFormDataBeforeSave()
```

### تضارب الفصل (Class Conflict)
```
✓ التحقق: لا يمكن لنفس الفصل أن تكون له حصتين في نفس اليوم والوقت
✓ المصدر: NoClassConflict Rule
✓ الفحص يتم في:
  - CreateSchoolSchedule::mutateFormDataBeforeCreate()
  - EditSchoolSchedule::mutateFormDataBeforeSave()
```

### آلية التحقق
```php
// مثال من SchoolScheduleValidator

public static function hasTeacherConflict(
    int $teacherClassId,
    int $dayId,
    int $lessonTimeId,
    ?int $excludeScheduleId = null
): bool
```

**المنطق:**
1. الحصول على المعلم من خلال teacherClass
2. البحث عن جداول أخرى للمعلم نفسه في نفس اليوم والحصة
3. استثناء الجدول الحالي عند التعديل (باستخدام $excludeScheduleId)
4. إذا كان يوجد تضارب → ترجع true مع إظهار تنبيه

---

## 💻 كيفية الاستخدام

### 1. إضافة جدول جديد
```
أ. ادخل إلى قسم "الجدول الدراسي"
ب. اضغط على زر "إضافة جديد"
ج. اختر:
   - معلم - مادة - فصل - قسم
   - اليوم
   - الحصة
د. إذا كان هناك تضارب، ستظهر رسالة خطأ واضحة
ه. اضغط "حفظ"
```

### 2. تعديل جدول موجود
```
أ. اختر الجدول من القائمة
ب. اضغط على "تعديل"
ج. غير البيانات المطلوبة
د. يتم التحقق من التضاربات مجدداً
ه. اضغط "حفظ"
```

### 3. حذف جدول
```
أ. اختر الجدول من القائمة
ب. اضغط على "تعديل"
ج. اضغط على "حذف"
د. أكد الحذف
```

---

## 📊 عرض البيانات في الجدول

يتم عرض المعلومات التالية:
| الحقل | الوصف |
|-------|-------|
| اليوم | اسم اليوم بالعربية |
| رقم الحصة | رقم الحصة أو "استراحة" |
| من | وقت البداية |
| إلى | وقت النهاية |
| اسم المعلم | اسم المعلم المسؤول |
| المادة | اسم المادة |
| الصف | الصف الدراسي (مثل: الأول الثانوي) |
| القسم | القسم (مثل: أ، ب، ج) |

---

## 🔧 التخصيص

### تغيير تنسيق عرض معلم-مادة-فصل-قسم
**الملف:** `app/Filament/Resources/SchoolSchedules/Schemas/SchoolScheduleForm.php`

ابحث عن:
```php
$label = sprintf(
    '%s - %s - %s - %s',
    $teacherClass->teacher->name,
    $teacherClass->subject->name,
    $teacherClass->classModel->grade->name,
    $teacherClass->classModel->section->name
);
```

غيّر الترتيب حسب احتياجاتك.

### إضافة فلاتر إضافية
**الملف:** `app/Filament/Resources/SchoolSchedules/Tables/SchoolScheduleTable.php`

أضف في مصفوفة `filters`:
```php
->filters([
    Tables\Filters\SelectFilter::make('day_id')
        ->relationship('day', 'day_name_ar'),
    // إضافة فلاتر أخرى...
])
```

### إضافة إجراءات (Actions)
**الملف:** `app/Filament/Resources/SchoolSchedules/Tables/SchoolScheduleTable.php`

أضف في مصفوفة `actions`:
```php
->actions([
    Tables\Actions\EditAction::make(),
    Tables\Actions\DeleteAction::make(),
    // إضافة إجراءات أخرى...
])
```

---

## 📧 رسائل الخطأ

### رسائل الخطأ بالعربية
تم إضافتها في `lang/ar.json`:

```json
"validation": {
    "teacher_conflict": "المعلم لديه حصة أخرى في نفس اليوم والوقت",
    "class_conflict": "الفصل لديه حصة أخرى في نفس اليوم والوقت"
}
```

---

## 🐛 معالجة الأخطاء

### عند حدوث تضارب:
1. يتم عرض تنبيه أحمر بعنوان واضح
2. تظهر رسالة توضيحية تحتوي على:
   - نوع التضارب (معلم أو فصل)
   - البيانات المتضاربة
3. لا يتم حفظ البيانات
4. يعود المستخدم لصفحة التعديل

---

## 📝 ملاحظات مهمة

### 1. ترتيب الأيام
```
الأيام يتم ترتيبها تلقائياً حسب `day_order`:
- السبت (1)
- الأحد (2)
- الاثنين (3)
- ... إلخ
```

### 2. الاستراحات
```
يتم تمييز الاستراحات تلقائياً:
- إذا كانت `is_break = true`
- يظهر "استراحة" بدلاً من رقم الحصة
```

### 3. البحث والترتيب
```
يمكن البحث عن:
- اسم المعلم
- المادة
- الصف
- القسم
- اليوم
```

### 4. الصلاحيات (Permissions)
```
اسم الصلاحية: 'school-schedules'
يتم استخدام Trait: HasResourcePermissions
```

---

## 🚀 خطوات الإعداد النهائي

1. **تشغيل الـ Migrations**
   ```bash
   # يجب إنشاء جداول أولاً (يدوياً أو عبر migration)
   ```

2. **إدراج البيانات الأساسية**
   ```sql
   INSERT INTO days (day_name_ar, day_order) VALUES
   ('السبت', 1),
   ('الأحد', 2),
   ('الاثنين', 3),
   ('الثلاثاء', 4),
   ('الأربعاء', 5),
   ('الخميس', 6);

   INSERT INTO lesson_times (period_number, start_time, end_time, is_break) VALUES
   (1, '08:00:00', '08:45:00', 0),
   (2, '08:45:00', '09:30:00', 0),
   (NULL, '09:30:00', '09:45:00', 1),  -- استراحة
   (3, '09:45:00', '10:30:00', 0),
   -- ... إضافة باقي الحصص
   ```

3. **التحقق من وجود جداول المتطلبات**
   - employees
   - teacher_classes
   - subjects
   - classes
   - grades
   - sections

4. **تفعيل الصلاحيات (إذا كنت تستخدم نظام صلاحيات)**
   - أضف صلاحية 'school-schedules' للأدوار المناسبة

---

## 📞 الدعم التقني

في حالة مواجهة مشاكل:

1. **تحقق من وجود الجداول** في قاعدة البيانات
2. **تحقق من العلاقات** بين الموديلات
3. **راجع ملفات الـ Logs** في `storage/logs/`
4. **تأكد من إضافة الصلاحيات** للمستخدم
