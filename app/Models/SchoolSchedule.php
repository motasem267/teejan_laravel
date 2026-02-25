<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSchedule extends Model
{
    protected $table = 'school_schedules';

    protected $fillable = [
        'teacher_class_id',
        'day_id',
        'lesson_time_id',
    ];

    public $timestamps = false;

    /**
     * Get the teacher class (teacher, subject, class, section).
     */
    public function teacherClass(): BelongsTo
    {
        return $this->belongsTo(TeacherClass::class, 'teacher_class_id');
    }

    /**
     * Get the day.
     */
    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class, 'day_id');
    }

    /**
     * Get the lesson time.
     */
    public function lessonTime(): BelongsTo
    {
        return $this->belongsTo(LessonTime::class, 'lesson_time_id');
    }

    /**
     * Get the teacher through teacher_class.
     */
    public function teacher()
    {
        return $this->teacherClass->teacher;
    }

    /**
     * Get the subject through teacher_class.
     */
    public function subject()
    {
        return $this->teacherClass->subject;
    }

    /**
     * Get the class model through teacher_class.
     */
    public function classModel()
    {
        return $this->teacherClass->classModel;
    }
}
