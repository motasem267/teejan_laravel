<?php

namespace App\Filament\Resources\Marks\Pages;

use App\Filament\Resources\Marks\MarkResource;
use App\Models\mark;
use App\Models\StudentEnrollment;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMark extends CreateRecord
{
    protected static string $resource = MarkResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('حفظ')
                ->color('primary'),
            $this->getCancelFormAction()
                ->label('إلغاء'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $enrollmentId = $data['student_inrollment_id'] ?? null;
        $subjectId    = $data['subject_id'] ?? null;
        $periodId     = $data['AcademicPeriodID'] ?? null;
        $fullMark     = $data['full_mark'] ?? null;
        $studentMark  = $data['student_mark'] ?? null;

        // التحقق من أن الدرجة لا تتجاوز الدرجة الكبرى
        if ($fullMark !== null && $studentMark !== null && (float) $studentMark > (float) $fullMark) {
            Notification::make()
                ->danger()
                ->title('الدرجة تتجاوز الحد الأقصى')
                ->body("الدرجة ({$studentMark}) أكبر من الدرجة الكبرى ({$fullMark}).")
                ->persistent()
                ->send();
            $this->halt();
        }

        // التحقق من عدم وجود درجة مكررة
        if ($enrollmentId && $subjectId && $periodId) {
            if (mark::markExists((int) $enrollmentId, (int) $subjectId, (int) $periodId)) {
                $enrollment = StudentEnrollment::with('student')->find($enrollmentId);
                $name = $enrollment?->student?->full_name ?? '#' . $enrollmentId;

                Notification::make()
                    ->danger()
                    ->title('درجة مكررة')
                    ->body("الطالب [{$name}] لديه درجة مسجَّلة مسبقاً لهذه المادة في نفس الفترة الدراسية.")
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الدرجة بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}