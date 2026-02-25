<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year_label')
                    ->label('السنة الدراسية')
                    ->placeholder('مثال: 2024-2025')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                
                Toggle::make('is_active')
                    ->label('السنة الفعالة')
                    ->helperText('السنة الفعالة تستخدم كافتراضي في النظام')
                    ->default(false)
                    ->inline(false),
            ]);
    }
}
