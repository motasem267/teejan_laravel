<?php

namespace App\Filament\Resources\Marks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الدرجة')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('enrollment.student.full_name')
                            ->label('الطالب'),

                        TextEntry::make('enrollment.grade.name')
                            ->label('الصف'),

                        TextEntry::make('enrollment.academicYear.year_label')
                            ->label('السنة الدراسية'),

                        TextEntry::make('subject.name')
                            ->label('المادة'),

                        TextEntry::make('academicPeriod.PeriodName')
                            ->label('الفترة الدراسية'),

                        TextEntry::make('full_mark')
                            ->label('الدرجة الكبرى'),

                        TextEntry::make('student_mark')
                            ->label('درجة الطالب'),

                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
                    ]),
            ]);
    }
}
