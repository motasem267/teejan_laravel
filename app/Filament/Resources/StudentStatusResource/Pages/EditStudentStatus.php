<?php

namespace App\Filament\Resources\StudentStatusResource\Pages;

use App\Filament\Resources\StudentStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudentStatus extends EditRecord
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
        
        return $user->hasPermission('student_status.edit');
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        if (Auth::user() && method_exists(Auth::user(), 'hasPermission') && Auth::user()->hasPermission('student_status.delete')) {
            $actions[] = Actions\DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation()
                ->modalHeading('حذف حالة الطالب')
                ->modalDescription('هل أنت متأكد من حذف هذه الحالة؟ سيؤثر ذلك على جميع الطلبة المرتبطين بها.')
                ->modalSubmitActionLabel('نعم، حذف');
        }
        
        return $actions;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث حالة الطالب بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}