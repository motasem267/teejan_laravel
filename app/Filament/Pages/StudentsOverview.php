<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\grade;
use App\Models\Section;
use App\Models\student;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * إحصائيات الطلبة النشطين: الصفوف ← الشعب ← أسماء الطلبة.
 * الأعداد حسب قيود السنة الدراسية الفعالة.
 */
class StudentsOverview extends Page
{
    public const ACTIVE_STATUS_ID = 1;

    // قيمة خاصة للطلبة غير المقيدين في السنة الفعالة
    public const UNENROLLED = 'none';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'إحصائيات الطلبة';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';

    protected static ?int $navigationSort = 0;

    #[Url]
    public ?string $grade = null;

    #[Url]
    public ?string $section = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission('students.view');
    }

    public function getView(): string
    {
        return 'filament.pages.students-overview';
    }

    public function getTitle(): string
    {
        return 'الطلبة النشطون';
    }

    public function getSubheading(): ?string
    {
        $year = $this->activeYear()?->year_label;

        return $year ? 'حسب قيود العام الدراسي ' . $year : 'لا توجد سنة دراسية فعالة';
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [static::getUrl() => 'كل الصفوف'];

        if ($this->grade === self::UNENROLLED) {
            $crumbs[] = 'غير مقيدين';
        } elseif ($this->grade) {
            $gradeName = grade::find($this->grade)?->name ?? '';

            if ($this->section !== null) {
                $crumbs[static::getUrl(['grade' => $this->grade])] = $gradeName;
                $crumbs[] = $this->sectionName($this->section);
            } else {
                $crumbs[] = $gradeName;
            }
        }

        return $crumbs;
    }

    public function getLevel(): string
    {
        if ($this->grade === self::UNENROLLED || ($this->grade && $this->section !== null)) {
            return 'students';
        }

        return $this->grade ? 'sections' : 'grades';
    }

    public function getTotal(): int
    {
        return student::where('status_id', self::ACTIVE_STATUS_ID)->enrolledIn($this->activeYear()?->id)->count();
    }

    /**
     * بطاقات الصفوف مع عدد الطلبة في كل صف.
     */
    public function getGradeCards(): array
    {
        $counts = $this->enrollmentCounts()
            ->groupBy('e.grade_id')
            ->selectRaw('e.grade_id as gid, COUNT(DISTINCT e.student_id) as c')
            ->pluck('c', 'gid');

        $gradeIds = ClassModel::distinct()->pluck('grade_id')->merge($counts->keys())->unique();

        $cards = grade::whereIn('id', $gradeIds)->orderBy('id')->get()
            ->map(fn ($g) => [
                'label' => $g->name,
                'count' => (int) ($counts[$g->id] ?? 0),
                'url' => static::getUrl(['grade' => $g->id]),
                'hint' => $this->sectionsLabel(ClassModel::where('grade_id', $g->id)->count()),
            ])
            ->all();

        $unenrolled = $this->unenrolledQuery()->count();
        if ($unenrolled > 0) {
            $cards[] = [
                'label' => 'غير مقيدين',
                'count' => $unenrolled,
                'url' => static::getUrl(['grade' => self::UNENROLLED]),
                'hint' => 'بدون قيد في السنة الحالية',
                'muted' => true,
            ];
        }

        return $cards;
    }

    /**
     * بطاقات شعب الصف المختار.
     */
    public function getSectionCards(): array
    {
        $counts = $this->enrollmentCounts()
            ->where('e.grade_id', $this->grade)
            ->groupBy('e.section_id')
            ->selectRaw('e.section_id as sid, COUNT(DISTINCT e.student_id) as c')
            ->pluck('c', 'sid');

        $gradeName = grade::find($this->grade)?->name ?? '';

        $sectionIds = ClassModel::where('grade_id', $this->grade)->pluck('section_id')
            ->merge($counts->keys()->filter())
            ->unique();

        $cards = Section::whereIn('id', $sectionIds)->orderBy('name')->get()
            ->map(fn ($s) => [
                'label' => trim($gradeName . ' ' . $s->name),
                'count' => (int) ($counts[$s->id] ?? 0),
                'url' => static::getUrl(['grade' => $this->grade, 'section' => $s->id]),
            ])
            ->all();

        // قيود بدون شعبة
        $withoutSection = (int) ($counts[''] ?? 0);
        if ($withoutSection > 0) {
            $cards[] = [
                'label' => 'بدون شعبة',
                'count' => $withoutSection,
                'url' => static::getUrl(['grade' => $this->grade, 'section' => '0']),
                'muted' => true,
            ];
        }

        return $cards;
    }

    public function getGradeTotal(): int
    {
        return (int) $this->enrollmentCounts()
            ->where('e.grade_id', $this->grade)
            ->selectRaw('COUNT(DISTINCT e.student_id) as c')
            ->value('c');
    }

    /**
     * أسماء الطلبة في الشعبة المختارة (أو غير المقيدين).
     */
    public function getStudents(): Collection
    {
        if ($this->grade === self::UNENROLLED) {
            $query = $this->unenrolledQuery();
        } else {
            $yearId = $this->activeYear()?->id;
            $query = student::where('status_id', self::ACTIVE_STATUS_ID)
                ->whereHas('enrollments', fn (Builder $q) => $q
                    ->where('academic_year_id', $yearId)
                    ->where('grade_id', $this->grade)
                    ->when(
                        $this->section === '0',
                        fn ($q) => $q->whereNull('section_id'),
                        fn ($q) => $q->where('section_id', $this->section),
                    ));
        }

        return $query->with('parent')->orderBy('full_name')->get();
    }

    public function getListsUrl(): ?string
    {
        if (! StudentLists::canAccess()) {
            return null;
        }

        if ($this->grade === self::UNENROLLED) {
            return StudentLists::getUrl();
        }

        return StudentLists::getUrl(array_filter([
            'grade_id' => $this->grade,
            'section_id' => $this->section ?: null,
        ]));
    }

    public function getHeading(): string
    {
        return match ($this->getLevel()) {
            'sections' => grade::find($this->grade)?->name ?? 'الشعب',
            'students' => $this->grade === self::UNENROLLED
                ? 'طلبة غير مقيدين في السنة الحالية'
                : trim((grade::find($this->grade)?->name ?? '') . ' ' . $this->sectionName($this->section)),
            default => 'الطلبة النشطون',
        };
    }

    private function sectionsLabel(int $n): string
    {
        return match (true) {
            $n === 0 => 'بدون شعب',
            $n === 1 => 'شعبة واحدة',
            $n === 2 => 'شعبتان',
            $n <= 10 => $n . ' شعب',
            default => $n . ' شعبة',
        };
    }

    private function sectionName(?string $sectionId): string
    {
        return $sectionId === '0' ? 'بدون شعبة' : (Section::find($sectionId)?->name ?? '');
    }

    private function activeYear(): ?academic_years
    {
        return once(fn () => academic_years::getActive());
    }

    /**
     * قيود السنة الفعالة للطلبة النشطين فقط.
     */
    private function enrollmentCounts(): \Illuminate\Database\Query\Builder
    {
        return DB::table('student_enrollments as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->where('e.academic_year_id', $this->activeYear()?->id)
            ->where('s.status_id', self::ACTIVE_STATUS_ID);
    }

    private function unenrolledQuery(): Builder
    {
        $yearId = $this->activeYear()?->id;

        return student::where('status_id', self::ACTIVE_STATUS_ID)
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('academic_year_id', $yearId));
    }
}
