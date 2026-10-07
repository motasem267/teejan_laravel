<?php

namespace App\Filament\Resources\EmployeeEnrollments\Pages;

use App\Filament\Pages\EmployeePromotion;
use App\Filament\Resources\EmployeeEnrollments\EmployeeEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeEnrollments extends ListRecords
{
    protected static string $resource = EmployeeEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('promote')
                ->label('ترحيل الموظفين')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(EmployeePromotion::getUrl())
                ->visible(fn () => EmployeePromotion::canAccess()),

            Actions\CreateAction::make()
                ->label('قيد موظفين'),
        ];
    }
}
