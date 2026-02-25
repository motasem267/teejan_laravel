<?php

namespace App\Filament\Resources\StudentStatusResource\Pages;

use App\Filament\Resources\StudentStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStudentStatuses extends ListRecords
{
    protected static string $resource = StudentStatusResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }
        
        return $user->hasPermission('student_status.view');
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        if (Auth::user() && method_exists(Auth::user(), 'hasPermission') && Auth::user()->hasPermission('student_status.create')) {
            $actions[] = Actions\CreateAction::make()
                ->label('إضافة حالة جديدة');
        }
        
        return $actions;
    }
}