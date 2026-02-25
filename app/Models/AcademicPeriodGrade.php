<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicPeriodGrade extends Model
{
    public $timestamps = false;
    
    protected $table = 'academicperiod_grades';
    
    protected $fillable = [
        'AcademicPeriodID',
        'GradeID',
        'IsViewed',
    ];
    
    protected $casts = [
        'IsViewed' => 'boolean',
    ];

    /**
     * Get the academic period.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'AcademicPeriodID');
    }

    /**
     * Get the grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class, 'GradeID');
    }
}
