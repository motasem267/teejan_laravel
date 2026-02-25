<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeekResult extends Model
{
    protected $table = 'weekresult';
    
    protected $fillable = [
        'week',
        'month',
        'academic_year_id',
    ];
    
    public $timestamps = true;
    
    /**
     * Get the academic year that owns the week result.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }
    
    /**
     * Get week options
     */
    public static function getWeekOptions(): array
    {
        return [
            1 => 'اسبوع اول',
            2 => 'اسبوع ثاني',
            3 => 'اسبوع ثالث',
            4 => 'اسبوع رابع',
            5 => 'اسبوع خامس',
        ];
    }
    
    /**
     * Get month options
     */
    public static function getMonthOptions(): array
    {
        return [
            1 => 'يناير',
            2 => 'فبراير',
            3 => 'مارس',
            4 => 'ابريل',
            5 => 'مايو',
            6 => 'يونيو',
            7 => 'يوليو',
            8 => 'اغسطس',
            9 => 'سبتمبر',
            10 => 'اكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];
    }
}
