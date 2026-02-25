<?php

namespace App\Filament\Resources\GradeSubjects\Pages;

use App\Filament\Resources\GradeSubjects\GradeSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGradeSubjects extends ListRecords
{
    protected static string $resource = GradeSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
