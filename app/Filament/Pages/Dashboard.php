<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';
    
    protected static ?string $navigationLabel = 'الرئيسية';
    
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\QuickStatsWidget::class,
            \App\Filament\Widgets\EmployeeInfoWidget::class,
        ];
    }
}
