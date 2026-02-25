<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicPeriod extends Model
{
    public $timestamps = false;
    
    protected $table = 'academic_periods';
    
    protected $primaryKey = 'AcademicPeriodID';
    
    protected $fillable = [
        'PeriodName',
        'StartDate',
        'EndDate',
        'IsActive',
    ];
    
    protected $casts = [
        'IsActive' => 'boolean',
        'StartDate' => 'date',
        'EndDate' => 'date',
    ];
}
