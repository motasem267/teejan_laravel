<?php

namespace App\Filament\Resources\BonusTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BonusTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('bonus_type_name')
                    ->label('اسم نوع العلاوة'),
            ]);
    }
}
