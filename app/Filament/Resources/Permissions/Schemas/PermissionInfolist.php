<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PermissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('label'),
                TextEntry::make('parent.name')
                    ->label('Parent')
                    ->placeholder('-'),
            ]);
    }
}
