<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'grade_id',
        'section_id',
        'academic_year_id',
    ];

    public $timestamps = true;

    /**
     * الحصول على الطالب الذي ينتمي إليه هذا القيد.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(student::class);
    }

    /**
     * الحصول على الصف الدراسي الذي ينتمي إليه هذا القيد.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class);
    }

    /**
     * الحصول على الشعبة التي ينتمي إليها هذا القيد.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * الحصول على السنة الدراسية التي ينتمي إليها هذا القيد.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }

    /**
     * الحصول على جميع الدرجات المرتبطة بهذا القيد.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(mark::class, 'student_inrollment_id');
    }

    /**
     * @deprecated استخدم marks() بدلاً من هذه الدالة
     */
    public function getMarks()
    {
        return $this->marks()->get();
    }

    /**
     * التحقق مما إذا كان القيد موجودًا بالفعل لهذه المجموعة.
     */
    public static function enrollmentExists($studentId, $gradeId, $academicYearId, $excludeId = null): bool
    {
        $query = self::where('student_id', $studentId)
            ->where('grade_id', $gradeId)
            ->where('academic_year_id', $academicYearId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
