<?php

namespace App\Filament\Resources\GradeSubjects\Pages;

use App\Filament\Resources\GradeSubjects\GradeSubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGradeSubject extends EditRecord
{
    protected static string $resource = GradeSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
