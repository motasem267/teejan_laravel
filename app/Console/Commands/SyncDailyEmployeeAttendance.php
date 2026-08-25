<?php

namespace App\Console\Commands;

use App\Services\Attendance\DailyEmployeeAttendanceService;
use Illuminate\Console\Command;

class SyncDailyEmployeeAttendance extends Command
{
    protected $signature = 'attendance:sync-daily-employee-attendance {--date= : Process date in Y-m-d format}';

    protected $description = 'Record first check-in / last check-out for non-teaching employees from attlog';

    public function handle(DailyEmployeeAttendanceService $service): int
    {
        $date = $this->option('date');
        $result = $service->processForDate($date);

        $this->info(sprintf(
            'Done for %s. Employees recorded: %d, processed logs: %d',
            $result['date'],
            $result['employees_recorded'],
            $result['processed_logs'],
        ));

        return self::SUCCESS;
    }
}
