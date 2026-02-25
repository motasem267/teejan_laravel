<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $table = 'evaluations';
    
    protected $fillable = [
        'evaluation_type_id',
        'evaluator_id',
        'student_id',
        'subject_id',
        'grade_id',
        'section_id',
        'academic_year_id',
        'week_no',
    ];
    
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get the evaluation type.
     */
    public function evaluationType(): BelongsTo
    {
        return $this->belongsTo(EvaluationType::class, 'evaluation_type_id');
    }

    /**
     * Get the evaluator (employee).
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(student::class);
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class);
    }

    /**
     * Get the grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class);
    }

    /**
     * Get the section.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }

    /**
     * Get all student answers for this evaluation.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(EvaluationStudentAnswer::class, 'evaluation_id');
    }
}
