<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Day extends Model
{
    protected $table = 'days';

    protected $fillable = [
        'day_name_ar',
        'day_order',
    ];

    public $timestamps = false;

    /**
     * Get all school schedules for this day.
     */
    public function schoolSchedules(): HasMany
    {
        return $this->hasMany(SchoolSchedule::class, 'day_id');
    }
}
