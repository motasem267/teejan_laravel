<?php

namespace App\Filament\Resources\LessonTypes\Pages;

use App\Filament\Resources\LessonTypes\LessonTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLessonType extends CreateRecord
{
    protected static string $resource = LessonTypeResource::class;
}
