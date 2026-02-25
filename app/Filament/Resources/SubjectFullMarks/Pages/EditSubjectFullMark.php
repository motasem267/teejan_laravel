<?php

namespace App\Filament\Resources\SubjectFullMarks\Pages;

use App\Filament\Resources\SubjectFullMarks\SubjectFullMarkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectFullMark extends EditRecord
{
    protected static string $resource = SubjectFullMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
