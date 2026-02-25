<?php

namespace App\Filament\Resources\LessonTimes\Pages;

use App\Filament\Resources\LessonTimes\LessonTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonTimes extends ListRecords
{
    protected static string $resource = LessonTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
