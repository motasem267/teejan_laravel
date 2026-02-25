<?php

namespace App\Filament\Resources\Bonuses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BonusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('emp_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('bonus_type_id')
                    ->label('نوع العلاوة')
                    ->relationship('bonusType', 'bonus_type_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                Textarea::make('remark')
                    ->label('ملاحظات')
                    ->rows(3),
            ]);
    }
}
