<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ملاحظة: كانت هنا جدولة تلقائية لخط أنابيب الحضور، اتشالت — teejan_attendence
// (على جهاز منفصل متصل مباشرة بجهاز البصمة) هو الوحيد اللي يحسب الحضور توا،
// production بس يقرا النتيجة النهائية المنعكسة من هناك.
