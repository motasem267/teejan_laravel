<?php

namespace App\Filament\Resources\Marks\Pages;

use App\Filament\Resources\Marks\MarkResource;
use App\Models\mark;
use App\Models\StudentEnrollment;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMark extends EditRecord
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Pre-fill the virtual academic_year_id field from the enrollment so the
     * student selector shows options in the right academic year.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $enrollment = StudentEnrollment::find($data['student_inrollment_id'] ?? null);
        $data['academic_year_id'] = $enrollment?->academic_year_id;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        if ($enrollmentId && $subjectId && $periodId) {
            if (mark::markExists((int) $enrollmentId, (int) $subjectId, (int) $periodId, $this->record->id)) {
                Notification::make()
                    ->danger()
                    ->title('درجة مكررة')
                    ->body('توجد درجة مسجَّلة مسبقاً لهذا الطالب في نفس المادة والفترة الدراسية.')
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الدرجة بنجاح';
    }
}