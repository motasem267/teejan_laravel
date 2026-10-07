<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * حساب المستحق والمدفوع لكل ولي أمر حسب السنة الدراسية.
 *
 * - المستحق: رسم الاشتراك السنوي لصف كل ابن في السنة (حسب قيده).
 * - المدفوع: الأقساط السنوية المدفوعة (installment_type_id = 1).
 *   ملاحظة: عمود installments.academic_year يخزن رقم السنة (id) وليس اسمها.
 * عند ترك السنة فارغة تُحسب كل السنوات.
 */
class RevenueCalculator
{
    public const ANNUAL_INSTALLMENT_TYPE_ID = 1;

    private array $cache = [];

    /**
     * @return array{due: float, paid: float, remaining: float, percentage: float}
     */
    public function forParent(int $parentId, ?int $yearId): array
    {
        return $this->cache[$parentId . '|' . $yearId] ??= (function () use ($parentId, $yearId) {
            $due = (float) DB::selectOne('SELECT ' . self::dueSql($yearId !== null) . ' AS v', array_merge([$parentId], $yearId !== null ? [$yearId] : []))->v;
            $paid = (float) DB::selectOne('SELECT ' . self::paidSql($yearId !== null) . ' AS v', array_merge([$parentId], $yearId !== null ? [$yearId] : []))->v;

            return [
                'due' => $due,
                'paid' => $paid,
                'remaining' => $due - $paid,
                'percentage' => $due > 0 ? ($paid / $due) * 100 : 0,
            ];
        })();
    }

    /**
     * استعلام فرعي للمستحق. الربط: ? = parent_id ثم ? = academic_year_id (إن وجدت).
     * لاستخدامه داخل whereRaw على جدول parents استبدل أول ? بـ parents.id عبر $parentColumn.
     */
    public static function dueSql(bool $withYear, string $parentColumn = '?'): string
    {
        return '(SELECT COALESCE(SUM(asf.amount), 0)
            FROM student_enrollments se
            JOIN students s ON s.id = se.student_id
            JOIN annual_subscription_fees asf ON asf.grade_id = se.grade_id AND asf.academic_year_id = se.academic_year_id
            WHERE s.parent_id = ' . $parentColumn . ($withYear ? ' AND se.academic_year_id = ?' : '') . ')';
    }

    public static function paidSql(bool $withYear, string $parentColumn = '?'): string
    {
        return '(SELECT COALESCE(SUM(i.amount), 0)
            FROM installments i
            WHERE i.parent_id = ' . $parentColumn . '
            AND i.installment_type_id = ' . self::ANNUAL_INSTALLMENT_TYPE_ID . '
            AND i.payment_type_id IS NOT NULL' . ($withYear ? ' AND i.academic_year = ?' : '') . ')';
    }
}
