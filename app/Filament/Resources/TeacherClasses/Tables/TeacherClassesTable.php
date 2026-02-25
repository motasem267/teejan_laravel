<?php

namespace App\Filament\Resources\TeacherClasses\Tables;

use App\Models\academic_years;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeacherClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('المعلم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.name')
                    ->label('المادة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classModel.grade.name')
                    ->label('الصف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classModel.section.name')
                    ->label('السكشن')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academicYear.year_label')
                    ->label('السنة الدراسية')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(academic_years::getActiveId())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
