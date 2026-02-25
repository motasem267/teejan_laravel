<?php

namespace App\Filament\Resources\Marks\Tables;

use App\Models\academic_years;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),

                TextColumn::make('enrollment.student.full_name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('enrollment.grade.name')
                    ->label('الصف')
                    ->sortable(),

                TextColumn::make('enrollment.academicYear.year_label')
                    ->label('السنة الدراسية')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('المادة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('academicPeriod.PeriodName')
                    ->label('الفترة الدراسية')
                    ->searchable()
                    ->sortable(),

                     TextColumn::make('student_mark')
                    ->label('درجة الطالب')
                    ->sortable(),

                TextColumn::make('full_mark')
                    ->label('الدرجة الكبرى')
                    ->sortable(),

               

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->form([
                        Select::make('academic_year_id')
                            ->label('السنة الدراسية')
                            ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->default(academic_years::getActiveId())
                            ->placeholder('كل السنوات'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['academic_year_id'] ?? null,
                            fn (Builder $q, $yearId) => $q->whereHas(
                                'enrollment',
                                fn (Builder $e) => $e->where('academic_year_id', $yearId)
                            )
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!($data['academic_year_id'] ?? null)) {
                            return null;
                        }
                        $label = academic_years::find($data['academic_year_id'])?->year_label;
                        return $label ? 'السنة: ' . $label : null;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ImportAction::make()
                    ->label('استيراد درجات')
                    ->color('success')
                    ->importer(\App\Filament\Imports\MarkImporter::class)
                    ->job(null),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}