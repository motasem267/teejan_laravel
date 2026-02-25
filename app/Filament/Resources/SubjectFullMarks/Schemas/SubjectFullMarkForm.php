<?php

namespace App\Filament\Resources\SubjectFullMarks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectFullMarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academicperiodID')
                    ->label('الفترة الدراسية')
                    ->relationship('academicPeriod', 'PeriodName')
                    ->required()
                    ->preload()
                    ->native(false),
                    
                Select::make('gradeID')
                    ->label('الصف الدراسي')
                    ->relationship('grade', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                    
                Select::make('subjectId')
                    ->label('المادة الدراسية')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                    
                TextInput::make('FullMark')
                    ->label('الدرجة الكاملة')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->default(100),
            ]);
    }
}
