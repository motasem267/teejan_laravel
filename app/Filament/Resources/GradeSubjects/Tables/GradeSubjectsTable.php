<?php

namespace App\Filament\Resources\GradeSubjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GradeSubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                    
                TextColumn::make('grade.name')
                    ->label('الصف الدراسي')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('subject.name')
                    ->label('المادة الدراسية')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('subject.full_mark')
                    ->label('الدرجة الكاملة')
                    ->sortable(),
            ])
            ->filters([
                //
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
