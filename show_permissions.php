<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;

echo "الصلاحيات الرئيسية (Parent Permissions):\n";
echo "==========================================\n\n";

$parents = Permission::whereNull('parent_id')->orderBy('id')->get();

foreach ($parents as $parent) {
    echo "ID: {$parent->id} | Name: {$parent->name} | Label: {$parent->label}\n";
    
    $children = Permission::where('parent_id', $parent->id)->get();
    if ($children->isNotEmpty()) {
        echo "  الصلاحيات الفرعية:\n";
        foreach ($children as $child) {
            echo "    - {$child->name} ({$child->label})\n";
        }
    }
    echo "\n";
}
