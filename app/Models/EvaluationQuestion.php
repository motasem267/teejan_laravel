<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationQuestion extends Model
{
    protected $table = 'evaluation_questions';
    
    protected $fillable = [
        'evaluation_type_id',
        'label',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public $timestamps = false;

    /**
     * Get the evaluation type that owns the question.
     */
    public function evaluationType(): BelongsTo
    {
        return $this->belongsTo(EvaluationType::class, 'evaluation_type_id');
    }

    /**
     * Get all answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class, 'question_id');
    }

    /**
     * Get all student answers for this question.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(EvaluationStudentAnswer::class, 'question_id');
    }

    /**
     * Get all shared answers that can be used with this question (many-to-many).
     */
    public function sharedAnswers(): BelongsToMany
    {
        return $this->belongsToMany(
            EvaluationAnswer::class,
            'evaluation_answer_question',
            'evaluation_question_id',
            'evaluation_answer_id'
        );
    }
}
