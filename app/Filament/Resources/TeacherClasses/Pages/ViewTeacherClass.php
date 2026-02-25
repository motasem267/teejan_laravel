<?php

namespace App\Filament\Resources\TeacherClasses\Pages;

use App\Filament\Resources\TeacherClasses\TeacherClassResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherClass extends ViewRecord
{
    protected static string $resource = TeacherClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
