<?php

namespace App\Filament\Resources\SubjectFullMarks\Pages;

use App\Filament\Resources\SubjectFullMarks\SubjectFullMarkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjectFullMarks extends ListRecords
{
    protected static string $resource = SubjectFullMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
