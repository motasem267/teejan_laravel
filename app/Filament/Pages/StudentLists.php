<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\grade;
use App\Models\Section;
use App\Models\student;
use App\Models\StudentStatus;
use App\Services\StudentListExportService;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class StudentLists extends Page implements HasForms
{
    use InteractsWithForms;

    public const PERMISSION = 'student-lists.export';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'قوائم الطلبة';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'استخراج قوائم الطلبة';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission(self::PERMISSION);
    }

    public function mount(): void
    {
        // يمكن فتح الصفحة من صفحة إحصائيات الطلبة مع تحديد الصف والشعبة مسبقاً
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
            'grade_id' => request()->integer('grade_id') ?: null,
            'section_id' => request()->integer('section_id') ?: null,
            'status_id' => StudentStatus::where('name', 'نشط')->value('id'),
            'columns' => StudentListExportService::DEFAULT_COLUMNS,
            'blank_columns' => [],
            'grouping' => 'per_class',
            'sort' => 'name',
            'orientation' => 'portrait',
            'format' => StudentListExportService::FORMAT_PDF,
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.student-lists';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make('الطلبة المطلوبون')
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('السنة الدراسية')
                            ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->required(),

                        Select::make('grade_id')
                            ->label('الصف')
                            ->options(fn () => grade::orderBy('id')->pluck('name', 'id'))
                            ->placeholder('كل الصفوف')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                        Select::make('section_id')
                            ->label('الشعبة')
                            ->options(fn (Get $get) => $get('grade_id')
                                ? Section::query()
                                    ->whereIn('id', ClassModel::where('grade_id', $get('grade_id'))->select('section_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                : [])
                            ->placeholder('كل الشعب')
                            ->disabled(fn (Get $get) => ! $get('grade_id')),

                        Select::make('status_id')
                            ->label('حالة الطالب')
                            ->options(fn () => StudentStatus::pluck('name', 'id'))
                            ->placeholder('كل الحالات'),
                    ])
                    ->columns(4),

                SchemaSection::make('أعمدة القائمة')
                    ->description('اختر الأعمدة اللي تبيها تظهر في القائمة')
                    ->schema([
                        CheckboxList::make('columns')
                            ->hiddenLabel()
                            ->options(StudentListExportService::columnOptions())
                            ->columns(4)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->required()
                            ->validationMessages(['required' => 'اختر عموداً واحداً على الأقل']),

                        TagsInput::make('blank_columns')
                            ->label('أعمدة فارغة إضافية')
                            ->placeholder('اكتب عنوان العمود واضغط Enter')
                            ->helperText('أعمدة فاضية للكتابة باليد، مثلاً: الحضور، التوقيع، ملاحظات')
                            ->suggestions(['الحضور', 'التوقيع', 'ملاحظات', 'الدرجة', 'استلم']),
                    ]),

                SchemaSection::make('تنسيق القائمة')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان القائمة')
                            ->placeholder('قائمة الطلبة')
                            ->maxLength(100),

                        ToggleButtons::make('grouping')
                            ->label('التقسيم')
                            ->options([
                                'per_class' => 'قائمة لكل فصل',
                                'single' => 'قائمة واحدة',
                            ])
                            ->icons([
                                'per_class' => 'heroicon-o-squares-2x2',
                                'single' => 'heroicon-o-bars-3',
                            ])
                            ->inline()
                            ->required(),

                        ToggleButtons::make('sort')
                            ->label('الترتيب حسب')
                            ->options([
                                'name' => 'الاسم',
                                'id' => 'رقم الطالب',
                            ])
                            ->inline()
                            ->required(),

                        ToggleButtons::make('format')
                            ->label('صيغة الملف')
                            ->options([
                                StudentListExportService::FORMAT_PDF => 'PDF',
                                StudentListExportService::FORMAT_XLSX => 'Excel',
                            ])
                            ->icons([
                                StudentListExportService::FORMAT_PDF => 'heroicon-o-document-text',
                                StudentListExportService::FORMAT_XLSX => 'heroicon-o-table-cells',
                            ])
                            ->inline()
                            ->required()
                            ->live(),

                        ToggleButtons::make('orientation')
                            ->label('اتجاه الصفحة')
                            ->options([
                                'portrait' => 'طولي',
                                'landscape' => 'عرضي',
                            ])
                            ->inline()
                            ->required()
                            ->visible(fn (Get $get) => $get('format') === StudentListExportService::FORMAT_PDF),
                    ])
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 5]),
            ])
            ->statePath('data');
    }

    public function generate(): mixed
    {
        $data = $this->form->getState();

        // الحفاظ على ترتيب الأعمدة كما هو معرّف في الخدمة
        $columns = array_values(array_intersect(array_keys(StudentListExportService::COLUMNS), $data['columns'] ?? []));
        $blankColumns = array_values(array_filter(array_map('trim', $data['blank_columns'] ?? [])));

        $groups = $this->buildGroups($data);
        $count = collect($groups)->sum(fn ($g) => $g['rows']->count());

        if ($count === 0) {
            Notification::make()->warning()->title('لا يوجد طلبة مطابقين للاختيار')->send();

            return null;
        }

        $meta = [
            'title' => trim((string) ($data['title'] ?? '')) ?: 'قائمة الطلبة',
            'year' => academic_years::find($data['academic_year_id'])?->year_label,
        ];

        try {
            $service = app(StudentListExportService::class);
            $format = $data['format'];
            $filename = StudentListExportService::filename($format);

            Notification::make()->success()->title('تم استخراج قائمة بـ ' . $count . ' طالب')->send();

            if ($format === StudentListExportService::FORMAT_XLSX) {
                $path = $service->xlsx($groups, $columns, $blankColumns, $meta);

                return response()->download($path, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend();
            }

            $content = $service->pdf($groups, $columns, $blankColumns, $meta, ($data['orientation'] ?? 'portrait') === 'landscape');

            return response()->streamDownload(fn () => print($content), $filename, ['Content-Type' => 'application/pdf']);
        } catch (\Throwable $e) {
            report($e);

            Notification::make()->danger()->title('تعذر استخراج القائمة')->body($e->getMessage())->send();

            return null;
        }
    }

    /**
     * @return array<int, array{title: string, rows: Collection}>
     */
    private function buildGroups(array $data): array
    {
        $yearId = $data['academic_year_id'];
        $gradeId = $data['grade_id'] ?? null;
        $sectionId = $data['section_id'] ?? null;

        $students = student::query()
            ->when($gradeId || $sectionId, fn ($query) => $query->whereHas('enrollments', fn (Builder $q) => $q
                ->where('academic_year_id', $yearId)
                ->when($gradeId, fn ($q) => $q->where('grade_id', $gradeId))
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))))
            ->when($data['status_id'] ?? null, fn ($q, $id) => $q->where('status_id', $id))
            ->with([
                'parent',
                'status',
                'enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)->with(['grade', 'section']),
            ])
            ->get();

        $rows = $students->map(fn (student $s) => [$s, $s->enrollments->first()]);

        $sortRows = fn (Collection $rows) => $rows
            ->sortBy(fn ($row) => ($data['sort'] ?? 'name') === 'id' ? sprintf('%012d', $row[0]->id) : (string) $row[0]->full_name)
            ->values();

        if (($data['grouping'] ?? 'per_class') === 'single') {
            $title = $gradeId
                ? collect([grade::find($gradeId)?->name, $sectionId ? Section::find($sectionId)?->name : null])->filter()->implode(' - ')
                : 'كل الطلبة';

            return [['title' => $title, 'rows' => $sortRows($rows)]];
        }

        return $rows
            ->groupBy(fn ($row) => $row[1] ? $row[1]->grade_id . '-' . $row[1]->section_id : 'none')
            ->sortBy(fn ($group, $key) => $key === 'none'
                ? [PHP_INT_MAX, '']
                : [$group->first()[1]->grade_id, (string) $group->first()[1]->section?->name])
            ->map(fn ($group, $key) => [
                'title' => $key === 'none'
                    ? 'طلبة غير مقيدين في هذه السنة'
                    : collect([$group->first()[1]->grade?->name, $group->first()[1]->section?->name])->filter()->implode(' - '),
                'rows' => $sortRows($group),
            ])
            ->values()
            ->all();
    }
}
