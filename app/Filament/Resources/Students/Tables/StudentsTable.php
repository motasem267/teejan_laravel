<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Actions\IdCardActions;
use App\Models\academic_years;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                    
                TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('national_id')
                    ->label('الرقم الوطني')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('parent.name')
                    ->label('ولي الأمر')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status.name')
                    ->label('الحالة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'نشط' => 'success',
                        'مُعلَّق' => 'warning',
                        'خارج' => 'danger',
                        'منقول' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('mother_phone')
                    ->label('هاتف الأم')
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // الافتراضي: الطلبة المقيدين في السنة الفعالة (امسح الفلتر لعرض كل الطلبة)
                SelectFilter::make('academic_year')
                    ->label('مقيد في السنة الدراسية')
                    ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                    ->default(academic_years::getActiveId())
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $yearId) => $q->enrolledIn($yearId),
                    )),

                SelectFilter::make('status_id')
                    ->label('فلترة حسب الحالة')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('جميع الحالات'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                IdCardActions::studentRecord(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    IdCardActions::studentsBulk(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
