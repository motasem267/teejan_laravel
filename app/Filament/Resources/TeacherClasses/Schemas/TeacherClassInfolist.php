<?php

namespace App\Filament\Resources\TeacherClasses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherClassInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('teacher.name')
                    ->label('Teacher'),
                TextEntry::make('subject.name')
                    ->label('Subject'),
                TextEntry::make('grade.name')
                    ->label('Grade'),
                TextEntry::make('section.id')
                    ->label('Section'),
            ]);
    }
}
