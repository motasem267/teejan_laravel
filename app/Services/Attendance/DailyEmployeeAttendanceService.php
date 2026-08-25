<?php

namespace App\Services\Attendance;

use App\Models\DailyEmployeeAttendance;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * يسجل حضور الموظفين غير المعلمين: أول بصمة في اليوم = دخول، آخر بصمة = خروج.
 * بلا مقارنة بجدول أوقات، لأنه ما فماش وقت متوقع محدد لهذي الفئة.
 */
class DailyEmployeeAttendanceService
{
    public function processForDate(null|string|DateTimeInterface $date = null): array
    {
        $date = $this->normalizeDate($date);
        $attlog = config('attendance.attlog');

        $employeeIds = Employee::query()
            ->whereDoesntHave('teacherClasses')
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            return [
                'date' => $date->toDateString(),
                'employees_recorded' => 0,
                'processed_logs' => 0,
            ];
        }

        $employeesRecorded = 0;
        $processedLogs = 0;

        foreach ($employeeIds as $employeeId) {
            $dayLogs = DB::table($attlog['table'])
                ->where($attlog['employee_column'], $employeeId)
                ->whereDate($attlog['timestamp_column'], $date->toDateString())
                ->orderBy($attlog['timestamp_column'])
                ->orderBy('id')
                ->get();

            if ($dayLogs->isEmpty()) {
                continue;
            }

            $firstCheckIn = $dayLogs->first()->{$attlog['timestamp_column']};
            $lastCheckOut = $dayLogs->count() > 1
                ? $dayLogs->last()->{$attlog['timestamp_column']}
                : null;

            DailyEmployeeAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date->toDateString()],
                [
                    'first_check_in' => $firstCheckIn,
                    'last_check_out' => $lastCheckOut,
                    'status' => 'present',
                ],
            );

            $employeesRecorded++;

            $unprocessedIds = $dayLogs
                ->reject(fn ($log) => (int) ($log->{$attlog['processed_column']} ?? 0) === 1)
                ->pluck('id');

            if ($unprocessedIds->isNotEmpty()) {
                DB::table($attlog['table'])
                    ->whereIn('id', $unprocessedIds)
                    ->update([$attlog['processed_column'] => 1]);

                $processedLogs += $unprocessedIds->count();
            }
        }

        return [
            'date' => $date->toDateString(),
            'employees_recorded' => $employeesRecorded,
            'processed_logs' => $processedLogs,
        ];
    }

    protected function normalizeDate(null|string|DateTimeInterface $date): CarbonImmutable
    {
        return $date instanceof DateTimeInterface
            ? CarbonImmutable::instance($date)
            : CarbonImmutable::parse($date ?? now());
    }
}
