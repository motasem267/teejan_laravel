<?php

namespace App\Filament\Resources\StudentEnrollments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentEnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم القيد')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('student.full_name')
                    ->label('الطالب')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('student.national_id')
                    ->label('الرقم الوطني')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('grade.name')
                    ->label('الصف الدراسي')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('section.name')
                    ->label('الشعبة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('academicYear.year_label')
                    ->label('السنة الدراسية')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('الصف')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('section_id')
                    ->label('الشعبة')
                    ->relationship('section', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(\App\Models\academic_years::getActiveId())
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
            ])
            ->defaultSort('id', 'desc');
    }
}
