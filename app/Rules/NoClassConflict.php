<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\SchoolSchedule;
use App\Models\TeacherClass;

class NoClassConflict implements ValidationRule
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
        // Get the teacher class with class and section info
        $teacherClass = TeacherClass::with('classModel')->findOrFail($this->teacherClassId);
        $classId = $teacherClass->class_id;

        // Check if this class/section has another schedule at the same time
        $query = SchoolSchedule::whereHas('teacherClass', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })
            ->where('day_id', $this->dayId)
            ->where('lesson_time_id', $this->lessonTimeId);

        // Exclude current schedule if updating
        if ($this->excludeScheduleId) {
            $query->where('id', '!=', $this->excludeScheduleId);
        }

        if ($query->exists()) {
            $classModel = $teacherClass->classModel;
            $fail(__('validation.class_conflict', [
                'class' => $classModel->grade->name . ' - ' . $classModel->section->name,
                'time' => $this->dayId,
            ]));
        }
    }
}
