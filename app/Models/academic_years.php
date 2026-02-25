<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class academic_years extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'year_label',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all marks for the academic year.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(mark::class, 'academic_year_id');
    }

     public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class);
    }
    
    /**
     * Get the active academic year
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }
    
    /**
     * Get the active academic year ID
     */
    public static function getActiveId()
    {
        $activeYear = self::getActive();
        return $activeYear ? $activeYear->id : null;
    }
}
