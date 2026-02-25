<?php

namespace App\Filament\Resources\TeacherClasses\Pages;

use App\Filament\Resources\TeacherClasses\TeacherClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeacherClasses extends ListRecords
{
    protected static string $resource = TeacherClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
