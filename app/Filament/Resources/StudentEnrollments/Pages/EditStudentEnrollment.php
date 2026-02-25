<?php

namespace App\Filament\Resources\StudentEnrollments\Pages;

use App\Filament\Resources\StudentEnrollments\StudentEnrollmentResource;
use App\Models\StudentEnrollment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStudentEnrollment extends EditRecord
{
    protected static string $resource = StudentEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeValidate(): void
    {
        $data = $this->data;

        // التحقق من عدم وجود قيد مكرر (باستثناء السجل الحالي)
        if (StudentEnrollment::enrollmentExists(
            $data['student_id'] ?? null,
            $data['grade_id'] ?? null,
            $data['academic_year_id'] ?? null,
            $this->record->id
        )) {
            Notification::make()
                ->title('خطأ في القيد')
                ->body('هذا الطالب مسجل مسبقاً في نفس الصف والسنة الدراسية')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
