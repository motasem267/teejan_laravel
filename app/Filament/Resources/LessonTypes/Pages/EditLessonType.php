<?php

namespace App\Filament\Resources\LessonTypes\Pages;

use App\Filament\Resources\LessonTypes\LessonTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLessonType extends EditRecord
{
    protected static string $resource = LessonTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
