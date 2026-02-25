<?php

namespace App\Filament\Resources\AnnualSubscriptionFees\Pages;

use App\Filament\Resources\AnnualSubscriptionFees\AnnualSubscriptionFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnnualSubscriptionFees extends ListRecords
{
    protected static string $resource = AnnualSubscriptionFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
