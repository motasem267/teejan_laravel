<?php

namespace App\Filament\Resources\StudentEnrollments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentEnrollmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('رقم القيد'),

                TextEntry::make('student.full_name')
                    ->label('الطالب'),

                TextEntry::make('student.national_id')
                    ->label('الرقم الوطني للطالب'),

                TextEntry::make('grade.name')
                    ->label('الصف الدراسي'),

                TextEntry::make('section.name')
                    ->label('الشعبة'),

                TextEntry::make('academicYear.year_label')
                    ->label('السنة الدراسية'),

                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i'),

                TextEntry::make('updated_at')
                    ->label('تاريخ آخر تحديث')
                    ->dateTime('Y-m-d H:i'),
            ]);
    }
}
