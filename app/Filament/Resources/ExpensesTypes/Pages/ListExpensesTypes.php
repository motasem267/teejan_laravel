<?php

namespace App\Filament\Resources\ExpensesTypes\Pages;

use App\Filament\Resources\ExpensesTypes\ExpensesTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpensesTypes extends ListRecords
{
    protected static string $resource = ExpensesTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
