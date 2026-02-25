<?php

namespace App\Filament\Resources\LessonTimes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class LessonTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('period_number')
                    ->label('رقم الحصة')
                    ->placeholder('1, 2, 3...')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->validationMessages([
                        'required' => 'يجب إدخال رقم الحصة',
                        'numeric' => 'يجب أن يكون رقماً',
                        'minValue' => 'الحد الأدنى هو 1',
                    ]),

                Select::make('lesson_type_id')
                    ->label('نوع الحصة')
                    ->relationship('lessonType', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'يجب اختيار نوع الحصة',
                    ]),

                TimePicker::make('start_time')
                    ->label('وقت البداية')
                    ->required()
                    ->seconds(false)
                    ->validationMessages([
                        'required' => 'يجب إدخال وقت البداية',
                    ]),

                TimePicker::make('end_time')
                    ->label('وقت النهاية')
                    ->required()
                    ->seconds(false)
                    ->after('start_time')
                    ->validationMessages([
                        'required' => 'يجب إدخال وقت النهاية',
                        'after' => 'وقت النهاية يجب أن يكون بعد وقت البداية',
                    ]),
            ]);
    }
}
