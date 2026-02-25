<?php

namespace App\Filament\Resources\AcademicPeriodGradeResource\Pages;

use App\Filament\Resources\AcademicPeriodGradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicPeriodGrade extends CreateRecord
{
    protected static string $resource = AcademicPeriodGradeResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
