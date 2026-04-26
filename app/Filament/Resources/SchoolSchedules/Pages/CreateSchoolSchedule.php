<?php

namespace App\Filament\Resources\SchoolSchedules\Pages;

use App\Filament\Resources\SchoolSchedules\SchoolScheduleResource;
use App\Http\Controllers\Filament\SchoolSchedules\SchoolScheduleValidator;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateSchoolSchedule extends CreateRecord
{
    protected static string $resource = SchoolScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validate conflicts
        $teacherClassId = $data['teacher_class_id'];
        $dayId = $data['day_id'];
        $lessonTimeId = $data['lesson_time_id'];

        // Check for teacher conflict
        if (SchoolScheduleValidator::hasTeacherConflict($teacherClassId, $dayId, $lessonTimeId)) {
            $teacherClass = TeacherClass::with('teacher')->findOrFail($teacherClassId);
            $message = 'المعلم ' . $teacherClass->teacher->name . ' لديه حصة أخرى في نفس اليوم والوقت';

            Notification::make()
                ->danger()
                ->title('تضارب جدول - معلم')
                ->body($message)
                ->send();

            throw ValidationException::withMessages([
                'teacher_class_id' => $message,
            ]);
        }

        // Check for class conflict
        if (SchoolScheduleValidator::hasClassConflict($teacherClassId, $dayId, $lessonTimeId)) {
            $teacherClass = TeacherClass::with('classModel.grade', 'classModel.section')->findOrFail($teacherClassId);
            $className = $teacherClass->classModel->grade->name . ' - ' . $teacherClass->classModel->section->name;
            $message = 'الفصل ' . $className . ' لديه حصة أخرى في نفس اليوم والوقت';

            Notification::make()
                ->danger()
                ->title('تضارب جدول - فصل')
                ->body($message)
                ->send();

            throw ValidationException::withMessages([
                'teacher_class_id' => $message,
            ]);
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الجدول بنجاح';
    }
}

