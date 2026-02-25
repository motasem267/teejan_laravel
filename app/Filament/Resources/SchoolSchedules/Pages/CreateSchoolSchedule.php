<?php

namespace App\Filament\Resources\SchoolSchedules\Pages;

use App\Filament\Resources\SchoolSchedules\SchoolScheduleResource;
use App\Http\Controllers\Filament\SchoolSchedules\SchoolScheduleValidator;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

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
        if (SchoolScheduleValidator::hasClassConflict($teacherClassId, $dayId, $lessonTimeId)) {
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الجدول بنجاح';
    }
}

