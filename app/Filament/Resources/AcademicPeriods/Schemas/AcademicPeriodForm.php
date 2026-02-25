<?php

namespace App\Filament\Resources\AcademicPeriods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AcademicPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('PeriodName')
                    ->label('اسم الفترة الدراسية')
                    ->required()
                    ->maxLength(100)
                    ->helperText('الفترة الدراسية تُضاف هنا فقط، وتفعيل الرؤية للصفوف يتم من صفحة "ربط الفترات بالصفوف"'),
            ]);
    }
}
