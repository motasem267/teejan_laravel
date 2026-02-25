<?php

namespace App\Http\Controllers\Filament\SchoolSchedules;

use App\Models\SchoolSchedule;
use App\Models\TeacherClass;
use App\Rules\NoClassConflict;
use App\Rules\NoTeacherConflict;
use Illuminate\Support\Facades\Validator;

class SchoolScheduleValidator
{
    /**
     * Validate school schedule conflicts.
     *
     * @param array $data The schedule data to validate
     * @param int|null $excludeScheduleId The schedule ID to exclude from conflict checking (for updates)
     * @return array Validation errors if any
     */
    public static function validateConflicts(array $data, ?int $excludeScheduleId = null): array
    {
        $errors = [];

        if (!isset($data['teacher_class_id']) || !isset($data['day_id']) || !isset($data['lesson_time_id'])) {
            return $errors;
        }

        $teacherClassId = $data['teacher_class_id'];
        $dayId = $data['day_id'];
        $lessonTimeId = $data['lesson_time_id'];

        // Validate teacher conflict
        $teacherValidator = Validator::make(
            ['teacher_class_id' => $teacherClassId],
            [
                'teacher_class_id' => [
                    new NoTeacherConflict($teacherClassId, $dayId, $lessonTimeId, $excludeScheduleId)
                ]
            ],
            [
                'teacher_class_id.custom' => 'validation.teacher_conflict'
            ]
        );

        if ($teacherValidator->fails()) {
            $errors['teacher_conflict'] = $teacherValidator->errors()->first('teacher_class_id');
        }

        // Validate class conflict
        $classValidator = Validator::make(
            ['teacher_class_id' => $teacherClassId],
            [
                'teacher_class_id' => [
                    new NoClassConflict($teacherClassId, $dayId, $lessonTimeId, $excludeScheduleId)
                ]
            ],
            [
                'teacher_class_id.custom' => 'validation.class_conflict'
            ]
        );

        if ($classValidator->fails()) {
            $errors['class_conflict'] = $classValidator->errors()->first('teacher_class_id');
        }

        return $errors;
    }

    /**
     * Check if teacher has conflict.
     */
    public static function hasTeacherConflict(
        int $teacherClassId,
        int $dayId,
        int $lessonTimeId,
        ?int $excludeScheduleId = null
    ): bool {
        $teacherClass = TeacherClass::findOrFail($teacherClassId);
        $teacherId = $teacherClass->teacher_id;

        $query = SchoolSchedule::whereHas('teacherClass', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
            ->where('day_id', $dayId)
            ->where('lesson_time_id', $lessonTimeId);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->exists();
    }

    /**
     * Check if class has conflict.
     */
    public static function hasClassConflict(
        int $teacherClassId,
        int $dayId,
        int $lessonTimeId,
        ?int $excludeScheduleId = null
    ): bool {
        $teacherClass = TeacherClass::findOrFail($teacherClassId);
        $classId = $teacherClass->class_id;

        $query = SchoolSchedule::whereHas('teacherClass', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })
            ->where('day_id', $dayId)
            ->where('lesson_time_id', $lessonTimeId);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->exists();
    }
}
