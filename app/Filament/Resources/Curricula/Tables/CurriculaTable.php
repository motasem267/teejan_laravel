<?php

namespace App\Filament\Resources\Curricula\Tables;

use App\Models\Curriculum;
use App\Models\grade;
use App\Models\subject;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CurriculaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Curriculum::query()->with(['grade', 'subject']))
            ->columns([
                TextColumn::make('book_name')
                    ->label('اسم الكتاب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('الصف الدراسي')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('الصف الدراسي')
                    ->options(grade::orderBy('name')->pluck('name', 'id')),

                SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->options(subject::orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
