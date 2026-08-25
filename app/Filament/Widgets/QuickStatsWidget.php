<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\student;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('الطلبة النشطون', student::where('status_id', 1)->count())
                ->icon(Heroicon::OutlinedUsers)
                ->color('success'),

            Stat::make('الموظفين', Employee::count())
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('warning'),
        ];
    }
}
