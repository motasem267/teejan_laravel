<?php

namespace App\Filament\Resources\AcademicPeriodGradeResource\Pages;

use App\Filament\Resources\AcademicPeriodGradeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditAcademicPeriodGrade extends EditRecord
{
    protected static string $resource = AcademicPeriodGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
