<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectFullMark extends Model
{
    protected $table = 'subject_fullmarks';
    
    protected $fillable = [
        'academicperiodID',
        'subjectId',
        'gradeID',
        'FullMark',
    ];
    
    public $timestamps = false;

    /**
     * Get the academic period.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academicperiodID');
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class, 'subjectId');
    }

    /**
     * Get the grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class, 'gradeID');
    }
}
