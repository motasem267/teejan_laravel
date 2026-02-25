<?php

namespace App\Filament\Resources\GradeSubjects\Pages;

use App\Filament\Resources\GradeSubjects\GradeSubjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGradeSubject extends ViewRecord
{
    protected static string $resource = GradeSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
