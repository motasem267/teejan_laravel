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
        return once(fn () => self::where('is_active', true)->first());
    }

    public static function getActiveLabel(): ?string
    {
        return self::getActive()?->year_label;
    }

    /**
     * سنة دراسية وحدة بس تكون فعالة: تفعيل سنة يلغي تفعيل الباقي.
     */
    protected static function booted(): void
    {
        static::saved(function (self $year) {
            if ($year->is_active && ($year->wasChanged('is_active') || $year->wasRecentlyCreated)) {
                self::where('id', '!=', $year->id)->where('is_active', true)->update(['is_active' => false]);
            }
        });
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
