<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// نسخة احتياطية آلية لخط أنابيب الحضور — الزر اليدوي في teejan_attendence هو المسار الأساسي،
// هذي الجدولة تضمن تشغيله حتى لو ما حد فتح تطبيق الحضور.
Schedule::command('attendance:generate-daily-class-snapshot')->dailyAt('06:00');
Schedule::command('attendance:sync-daily-class-attendance')->everyFifteenMinutes();
Schedule::command('attendance:sync-daily-employee-attendance')->everyFifteenMinutes();
