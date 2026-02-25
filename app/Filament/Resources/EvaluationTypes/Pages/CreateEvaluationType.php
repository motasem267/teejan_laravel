<?php

namespace App\Filament\Resources\EvaluationTypes\Pages;

use App\Filament\Resources\EvaluationTypes\EvaluationTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluationType extends CreateRecord
{
    protected static string $resource = EvaluationTypeResource::class;
}
