<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AcademicYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('year_label')
                    ->label('السنة الدراسية'),
                
                TextEntry::make('is_active')
                    ->label('السنة الفعالة')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'نعم' : 'لا')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ]);
    }
}
