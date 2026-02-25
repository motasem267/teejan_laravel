<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class mark extends Model
{
    public $timestamps = true;

    protected $table = 'marks';

    protected $fillable = [
        'student_inrollment_id',
        'subject_id',
        'AcademicPeriodID',
        'full_mark',
        'student_mark',
    ];

    /**
     * Check if a mark already exists for this enrollment + subject + period.
     */
    public static function markExists(int $enrollmentId, int $subjectId, int $academicPeriodId, $excludeId = null): bool
    {
        $query = self::where('student_inrollment_id', $enrollmentId)
            ->where('subject_id', $subjectId)
            ->where('AcademicPeriodID', $academicPeriodId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get the enrollment (student + academic year + grade) that owns the mark.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_inrollment_id');
    }

    /**
     * Get the subject that the mark belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class);
    }

    /**
     * Get the academic period that the mark belongs to.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'AcademicPeriodID');
    }
}
