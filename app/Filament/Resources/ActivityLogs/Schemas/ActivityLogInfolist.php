<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('الرقم'),
                TextEntry::make('employee.name')
                    ->label('المستخدم')
                    ->placeholder('-'),
                TextEntry::make('action')
                    ->label('الإجراء'),
                TextEntry::make('description')
                    ->label('الوصف')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('model_type')
                    ->label('نوع النموذج')
                    ->placeholder('-'),
                TextEntry::make('model_id')
                    ->label('معرف النموذج')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ip_address')
                    ->label('عنوان IP')
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->label('المتصفح')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i:s'),
            ]);
    }
}
