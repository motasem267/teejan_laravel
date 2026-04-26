# حل مشكلة الأحرف العربية في PDF

## المشكلة
عند تحميل ملفات PDF من التقارير، كانت الأحرف العربية تظهر كعلامات استفهام (؟؟؟).

## السبب الجذري
DomPDF بشكل افتراضي لا يأتي مع دعم كامل للخطوط العربية. الخط الافتراضي DejaVu Sans له دعم محدود للعربية.

## الحل المطبق

### 1. تفعيل Font Subsetting
عدلنا ملف `config/dompdf.php`:
```php
'enable_font_subsetting' => true
```

هذا يسمح بتضمين الأحرف المستخدمة فقط في الملف بدلاً من كل الخط.

### 2. استخدام DejaVu Sans Font
حددنا الخط في ملفات PDF templates:
```css
font-family: DejaVu Sans, serif;
```

DejaVu Sans يأتي مع DomPDF بشكل افتراضي وله أحرف عربية أساسية.

### 3. إضافة خيارات UTF-8 الصحيحة
أضفنا إلى ReportController:
```php
->setOption('isHtml5ParserEnabled', true)
->setOption('isFontSubsettingEnabled', true)
->setOption('isRemoteEnabled', false)
->setOption('encoding', 'UTF-8')
```

### 4. إضافة Meta Tags
أضفنا meta tags صريحة في ملفات HTML:
```html
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
```

## للحصول على دعم عربي أفضل

إذا كنت تريد دعماً أفضل للعربية (خطوط أجمل)، يمكنك إضافة خط عربي:

1. تحميل خط TrueType عربي (مثل شاهد، جيزة، أو Simplified Arabic)
2. وضع الخط في `storage/fonts/`
3. تشغيل الأمر:
```bash
php vendor/dompdf/dompdf/load_font.php /path/to/font.ttf
```

4. ثم تحديث ملفات PDF templates لاستخدام الخط الجديد:
```css
font-family: 'شاهد', DejaVu Sans, serif;
```

## الملفات المعدلة
- `config/dompdf.php` - تفعيل font subsetting
- `app/Http/Controllers/ReportController.php` - إضافة خيارات UTF-8
- `resources/views/pdf/expenses-report.blade.php` - meta tags وخط
- `resources/views/pdf/salaries-report.blade.php` - meta tags وخط
- `resources/views/pdf/revenue-report.blade.php` - meta tags وخط
