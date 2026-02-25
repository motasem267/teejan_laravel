<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('رقم الطالب'),
                    
                TextEntry::make('full_name')
                    ->label('الاسم الكامل'),
                    
                TextEntry::make('national_id')
                    ->label('الرقم الوطني'),
                    
                TextEntry::make('parent.name')
                    ->label('ولي الأمر'),

                TextEntry::make('status.name')
                    ->label('حالة الطالب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'نشط' => 'success',
                        'مُعلَّق' => 'warning',
                        'خارج' => 'danger',
                        'منقول' => 'info',
                        default => 'gray',
                    }),
                    
                TextEntry::make('mother_phone')
                    ->label('هاتف الأم'),
            ]);
    }
}
