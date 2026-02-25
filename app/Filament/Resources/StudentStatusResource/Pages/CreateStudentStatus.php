<?php

namespace App\Filament\Resources\StudentStatusResource\Pages;

use App\Filament\Resources\StudentStatusResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudentStatus extends CreateRecord
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
        
        return $user->hasPermission('student_status.create');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء حالة الطالب بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}