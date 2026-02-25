<?php

namespace App\Filament\Resources\GradeSubjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class GradeSubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('gradeID')
                    ->label('الصف الدراسي')
                    ->relationship('grade', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                    
                Select::make('subjectID')
                    ->label('المادة الدراسية')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
            ]);
    }
}
