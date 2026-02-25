<?php

namespace App\Filament\Resources\TeacherClasses\Schemas;

use App\Models\academic_years;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Hidden;


class TeacherClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teacher_id')
                    ->label('المعلم')
                    ->relationship(
                        'teacher',
                        'name',
                        fn (Builder $query) => $query->whereHas('employeeType', fn ($q) => $q->where('type_name', 'LIKE', '%معلم%'))
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('class_id')
                    ->label('الصف (القريد + السكشن)')
                    ->relationship('classModel', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->grade->name . ' - ' . $record->section->name
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $class = \App\Models\ClassModel::find($state);
                        $set('section_id', $class?->section_id);
                    }) ,
                    
               Hidden::make('section_id')
                    ->dehydrated()
                    ->required(),

                Select::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(\App\Models\academic_years::getActiveId())
                    ->required()
                    ->searchable()
                    ->preload(),
                            ]);
    }
}
