<?php

namespace App\Filament\Resources\Parents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ParentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('الاسم'),
                TextEntry::make('phone')
                    ->label('رقم الهاتف'),
                TextEntry::make('address')
                    ->label('العنوان'),
            ]);
    }
}
