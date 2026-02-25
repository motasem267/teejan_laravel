<?php

namespace App\Filament\Resources\Days\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('day_name_ar')
                    ->label('اسم اليوم بالعربية')
                    ->placeholder('مثال: السبت، الأحد، الاثنين')
                    ->required()
                    ->maxLength(50)
                    ->validationMessages([
                        'required' => 'يجب إدخال اسم اليوم',
                        'maxLength' => 'اسم اليوم يجب أن لا يتجاوز 50 حرف',
                    ]),

                TextInput::make('day_order')
                    ->label('ترتيب اليوم')
                    ->placeholder('1, 2, 3...')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(7)
                    ->helperText('1 = السبت، 2 = الأحد، وهكذا')
                    ->validationMessages([
                        'required' => 'يجب إدخال ترتيب اليوم',
                        'numeric' => 'يجب أن يكون رقماً',
                        'minValue' => 'الحد الأدنى هو 1',
                        'maxValue' => 'الحد الأقصى هو 7',
                    ]),
            ]);
    }
}
