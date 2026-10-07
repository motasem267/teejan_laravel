<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EmployeeEnrollments\EmployeeEnrollmentResource;
use App\Models\academic_years;
use App\Filament\Pages\StudentsOverview;
use App\Models\Employee;
use App\Models\student;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickStatsWidget extends BaseWidget
{
    /**
     * الأعداد حسب قيود السنة الدراسية الفعالة.
     */
    protected function getStats(): array
    {
        $year = academic_years::getActiveLabel();
        $canViewEnrollments = EmployeeEnrollmentResource::canViewAny();

        return [
            Stat::make('الطلبة النشطون', student::where('status_id', StudentsOverview::ACTIVE_STATUS_ID)->enrolledIn()->count())
                ->icon(Heroicon::OutlinedUsers)
                ->color('success')
                ->description(StudentsOverview::canAccess() ? 'اضغط لعرض التوزيع على الصفوف' : $year)
                ->descriptionIcon(StudentsOverview::canAccess() ? Heroicon::ArrowLeft : null)
                ->url(StudentsOverview::canAccess() ? StudentsOverview::getUrl() : null),

            Stat::make('الموظفين', Employee::enrolledIn()->count())
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('warning')
                ->description($canViewEnrollments ? 'اضغط لعرض قيد الموظفين' : $year)
                ->descriptionIcon($canViewEnrollments ? Heroicon::ArrowLeft : null)
                ->url($canViewEnrollments ? EmployeeEnrollmentResource::getUrl('index') : null),
        ];
    }
}
