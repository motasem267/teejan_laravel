<?php

namespace App\Filament\Resources\LessonTimes\Pages;

use App\Filament\Resources\LessonTimes\LessonTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLessonTime extends EditRecord
{
    protected static string $resource = LessonTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // إذا كانت استراحة، اجعل period_number = null
        if (isset($data['is_break']) && $data['is_break']) {
            $data['period_number'] = null;
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الحصة بنجاح';
    }
}
