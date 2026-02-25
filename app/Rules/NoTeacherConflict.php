<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;

class NoTeacherConflict implements ValidationRule
{
    protected int $teacherClassId;
    protected int $dayId;
    protected int $lessonTimeId;
    protected ?int $excludeScheduleId;

    public function __construct(int $teacherClassId, int $dayId, int $lessonTimeId, ?int $excludeScheduleId = null)
    {
        $this->teacherClassId = $teacherClassId;
        $this->dayId = $dayId;
        $this->lessonTimeId = $lessonTimeId;
        $this->excludeScheduleId = $excludeScheduleId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the teacher through the teacher_class relationship
        $teacherClass = TeacherClass::findOrFail($this->teacherClassId);
        $teacherId = $teacherClass->teacher_id;

        // Check if this teacher has another schedule at the same time
        $query = SchoolSchedule::whereHas('teacherClass', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
            ->where('day_id', $this->dayId)
            ->where('lesson_time_id', $this->lessonTimeId);

        // Exclude current schedule if updating
        if ($this->excludeScheduleId) {
            $query->where('id', '!=', $this->excludeScheduleId);
        }

        if ($query->exists()) {
            $fail(__('validation.teacher_conflict', [
                'teacher' => $teacherClass->teacher->name,
                'time' => $this->dayId,
            ]));
        }
    }
}
