<?php

namespace App\Filament\Resources\SalaryTypes\Pages;

use App\Filament\Resources\SalaryTypes\SalaryTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalaryTypes extends ListRecords
{
    protected static string $resource = SalaryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
