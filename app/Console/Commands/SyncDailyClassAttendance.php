<?php

namespace App\Console\Commands;

use App\Services\Attendance\DailyClassAttendanceMatcherService;
use Illuminate\Console\Command;

class SyncDailyClassAttendance extends Command
{
    protected $signature = 'attendance:sync-daily-class-attendance {--date= : Process date in Y-m-d format}';

    protected $description = 'Match raw attlog entries against the daily class attendance snapshot';

    public function handle(DailyClassAttendanceMatcherService $matcherService): int
    {
        $date = $this->option('date');
        $result = $matcherService->processForDate($date);

        $this->info(sprintf(
            'Done for %s. Check-ins: %d, check-outs: %d, processed logs: %d',
            $result['date'],
            $result['matched_check_ins'],
            $result['matched_check_outs'],
            $result['processed_logs'],
        ));

        return self::SUCCESS;
    }
}