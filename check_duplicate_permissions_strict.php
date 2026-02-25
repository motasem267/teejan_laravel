<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

$resources = [
    'annual_subscription_fees',
    'week-results',
    'installment_types',
    'expenses_types',
    'bonus_types',
    'deduction_types',
    'lesson_types',
];

foreach ($resources as $resource) {
    $parents = Permission::where('name', $resource)->whereNull('parent_id')->get();
    if ($parents->count() > 1) {
        echo "==== $resource ====\n";
        foreach ($parents as $parent) {
            echo "Parent: id={$parent->id}, name={$parent->name}, label={$parent->label}\n";
        }
    }
}
