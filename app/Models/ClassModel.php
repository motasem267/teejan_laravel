<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    protected $table = 'classes';
    
    protected $fillable = [
        'grade_id',
        'section_id',
    ];
    
    public $timestamps = false;

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
     * Get all teacher classes.
     */
    public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class, 'class_id');
    }
}
