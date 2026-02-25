<?php

namespace App\Filament\Resources\LessonTimes\Pages;

use App\Filament\Resources\LessonTimes\LessonTimeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLessonTime extends CreateRecord
{
    protected static string $resource = LessonTimeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إذا كانت استراحة، اجعل period_number = null
        if (isset($data['is_break']) && $data['is_break']) {
            $data['period_number'] = null;
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الحصة بنجاح';
    }
}
