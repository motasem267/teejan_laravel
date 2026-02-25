<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDaysCalendar extends Model
{
    use HasFactory;

    protected $table = 'work_days_calendar';

    protected $fillable = [
        'month',
        'year',
        'work_days',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'work_days' => 'integer',
    ];
}
