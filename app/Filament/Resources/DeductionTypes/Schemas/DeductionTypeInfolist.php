<?php

namespace App\Filament\Resources\DeductionTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DeductionTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('deduction_type_name')
                    ->label('اسم نوع الخصم'),
            ]);
    }
}
