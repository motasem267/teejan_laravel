<?php

namespace App\Filament\Resources\BonusTypes\Pages;

use App\Filament\Resources\BonusTypes\BonusTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBonusTypes extends ListRecords
{
    protected static string $resource = BonusTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
