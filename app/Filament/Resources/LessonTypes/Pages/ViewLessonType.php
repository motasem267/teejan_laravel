<?php

namespace App\Filament\Resources\LessonTypes\Pages;

use App\Filament\Resources\LessonTypes\LessonTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLessonType extends ViewRecord
{
    protected static string $resource = LessonTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
