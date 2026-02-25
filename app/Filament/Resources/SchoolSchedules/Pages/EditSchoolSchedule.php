<?php

namespace App\Filament\Resources\SchoolSchedules\Pages;

use App\Filament\Resources\SchoolSchedules\SchoolScheduleResource;
use App\Http\Controllers\Filament\SchoolSchedules\SchoolScheduleValidator;
use App\Models\TeacherClass;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSchoolSchedule extends EditRecord
{
    protected static string $resource = SchoolScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate conflicts
        $teacherClassId = $data['teacher_class_id'];
        $dayId = $data['day_id'];
        $lessonTimeId = $data['lesson_time_id'];
        $scheduleId = $this->record->id;

        // Check for teacher conflict
        if (SchoolScheduleValidator::hasTeacherConflict($teacherClassId, $dayId, $lessonTimeId, $scheduleId)) {
            $teacherClass = TeacherClass::with('teacher')->findOrFail($teacherClassId);
            Notification::make()
                ->danger()
                ->title('تضارب جدول - معلم')
                ->body(
                    'المعلم ' . $teacherClass->teacher->name . ' لديه حصة أخرى في نفس اليوم والوقت'
                )
                ->send();

            throw new \Exception('تضارب في جدول المعلم');
        }

        // Check for class conflict
        if (SchoolScheduleValidator::hasClassConflict($teacherClassId, $dayId, $lessonTimeId, $scheduleId)) {
            $teacherClass = TeacherClass::with('classModel.grade', 'classModel.section')->findOrFail($teacherClassId);
            $className = $teacherClass->classModel->grade->name . ' - ' . $teacherClass->classModel->section->name;

            Notification::make()
                ->danger()
                ->title('تضارب جدول - فصل')
                ->body(
                    'الفصل ' . $className . ' لديه حصة أخرى في نفس اليوم والوقت'
                )
                ->send();

            throw new \Exception('تضارب في جدول الفصل');
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الجدول بنجاح';
    }
}

