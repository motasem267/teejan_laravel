<?php

namespace App\Filament\Resources\ExpensesTypes\Pages;

use App\Filament\Resources\ExpensesTypes\ExpensesTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpensesType extends CreateRecord
{
    protected static string $resource = ExpensesTypeResource::class;
}
