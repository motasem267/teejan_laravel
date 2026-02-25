<?php

namespace App\Filament\Resources\InstallmentTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstallmentTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('installment_type_name')
                    ->label('اسم نوع القسط'),
            ]);
    }
}
