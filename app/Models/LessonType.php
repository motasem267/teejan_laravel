<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonType extends Model
{
    protected $table = 'lesson_types';

    protected $fillable = [
        'name',
    ];

    public $timestamps = true;

    /**
     * Get all lesson times for this lesson type.
     */
    public function lessonTimes(): HasMany
    {
        return $this->hasMany(LessonTime::class, 'lesson_type_id');
    }
}
