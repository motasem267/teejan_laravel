<?php

namespace App\Filament\Resources\ExpensesTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpensesTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('expenses_type_name')
                    ->label('اسم نوع المصروف'),
            ]);
    }
}
