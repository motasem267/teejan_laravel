<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonTime extends Model
{
    protected $table = 'lesson_times';

    protected $fillable = [
        'period_number',
        'lesson_type_id',
        'start_time',
        'end_time',
    ];

    public $timestamps = false;

    /**
     * Get the lesson type that owns the lesson time.
     */
    public function lessonType(): BelongsTo
    {
        return $this->belongsTo(LessonType::class, 'lesson_type_id');
    }

    /**
     * Get all school schedules for this lesson time.
     */
    public function schoolSchedules(): HasMany
    {
        return $this->hasMany(SchoolSchedule::class, 'lesson_time_id');
    }
}
