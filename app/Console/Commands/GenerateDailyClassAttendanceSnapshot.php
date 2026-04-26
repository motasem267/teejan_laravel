<?php

namespace App\Console\Commands;

use App\Services\Attendance\DailyClassAttendanceSnapshotService;
use Illuminate\Console\Command;

class GenerateDailyClassAttendanceSnapshot extends Command
{
    protected $signature = 'attendance:generate-daily-class-snapshot {--date= : Snapshot date in Y-m-d format}';

    protected $description = 'Generate the daily class attendance snapshot for the selected date';

    public function handle(DailyClassAttendanceSnapshotService $snapshotService): int
    {
        $date = $this->option('date');
        $created = $snapshotService->generateForDate($date);

        $this->info("Daily snapshot ready. Rows inserted: {$created}");

        return self::SUCCESS;
    }
}