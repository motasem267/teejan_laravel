<?php

namespace App\Filament\Resources\TeacherClasses\Pages;

use App\Filament\Resources\TeacherClasses\TeacherClassResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeacherClass extends EditRecord
{
    protected static string $resource = TeacherClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
