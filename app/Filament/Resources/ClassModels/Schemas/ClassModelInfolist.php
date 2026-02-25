<?php

namespace App\Filament\Resources\ClassModels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClassModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('grade.name')
                    ->label('Grade'),
                TextEntry::make('section.name')
                    ->label('Section'),
            ]);
    }
}
