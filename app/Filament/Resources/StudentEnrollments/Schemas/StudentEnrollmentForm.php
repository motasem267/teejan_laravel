<?php

namespace App\Filament\Resources\StudentEnrollments\Schemas;

use App\Models\StudentEnrollment;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Select::make('grade_id')
                    ->label('الصف الدراسي')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('section_id')
                    ->label('الشعبة')
                    ->relationship('section', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(\App\Models\academic_years::getActiveId())
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}

