<?php

namespace App\Filament\Resources\Days\Pages;

use App\Filament\Resources\Days\DayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDay extends CreateRecord
{
    protected static string $resource = DayResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة اليوم بنجاح';
    }
}
