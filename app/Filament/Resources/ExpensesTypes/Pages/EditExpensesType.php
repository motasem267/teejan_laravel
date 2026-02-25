<?php

namespace App\Filament\Resources\ExpensesTypes\Pages;

use App\Filament\Resources\ExpensesTypes\ExpensesTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditExpensesType extends EditRecord
{
    protected static string $resource = ExpensesTypeResource::class;
}
