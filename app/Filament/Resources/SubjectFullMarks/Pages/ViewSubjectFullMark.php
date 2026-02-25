<?php

namespace App\Filament\Resources\SubjectFullMarks\Pages;

use App\Filament\Resources\SubjectFullMarks\SubjectFullMarkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSubjectFullMark extends ViewRecord
{
    protected static string $resource = SubjectFullMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
