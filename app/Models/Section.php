<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $table = 'sections';
    
    protected $fillable = [
        'name',
        'grade_id',
    ];
    
    public $timestamps = false;

    /**
     * Get the grade that owns the section.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class);
    }

    /**
     * Get all teacher classes for this section.
     */
    public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class, 'section_id');
    }

    /**
     * Get all evaluations for this section.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'section_id');
    }

    /**
     * Get all class models for this section.
     */
    public function classModels(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'section_id');
    }
}
