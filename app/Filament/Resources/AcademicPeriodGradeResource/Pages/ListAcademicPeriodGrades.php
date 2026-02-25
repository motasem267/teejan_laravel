<?php

namespace App\Filament\Resources\AcademicPeriodGradeResource\Pages;

use App\Filament\Resources\AcademicPeriodGradeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListAcademicPeriodGrades extends ListRecords
{
    protected static string $resource = AcademicPeriodGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة ربط جديد'),
        ];
    }
}
