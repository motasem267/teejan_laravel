<?php

namespace App\Filament\Resources\WeekResults\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WeekResultInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('الرقم'),
                TextEntry::make('week')
                    ->label('الأسبوع'),
                TextEntry::make('month')
                    ->label('الشهر'),
                TextEntry::make('academicYear.year_label')
                    ->label('السنة الدراسية'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i:s'),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i:s'),
            ]);
    }
}
