<?php

namespace App\Filament\Resources\LessonTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LessonTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('اسم نوع الحصة'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i:s'),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i:s'),
            ]);
    }
}
