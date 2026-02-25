<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationAnswer extends Model
{
    protected $table = 'evaluation_answers';
    
    protected $fillable = [
        'question_id',
        'label',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public $timestamps = false;

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }

    /**
     * Get all student answers using this answer.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(EvaluationStudentAnswer::class, 'answer_id');
    }

    /**
     * Get all questions that can use this answer (many-to-many).
     */
    public function sharedQuestions(): BelongsToMany
    {
        return $this->belongsToMany(
            EvaluationQuestion::class,
            'evaluation_answer_question',
            'evaluation_answer_id',
            'evaluation_question_id'
        );
    }
}
