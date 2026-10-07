<x-filament-panels::page>
    <style>
        .so-summary { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
        .so-summary-count { font-size: 2rem; font-weight: 800; line-height: 1; color: var(--primary-600); }
        .so-summary-label { font-size: .875rem; color: var(--gray-500); margin-top: .35rem; }

        .so-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); }
        .so-card {
            position: relative; display: block; padding: 1.25rem 1.25rem 1.1rem; border-radius: .9rem;
            background: #fff; border: 1px solid var(--gray-200); text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            overflow: hidden;
        }
        .so-card::before {
            content: ""; position: absolute; inset-inline-start: 0; top: 0; bottom: 0; width: 4px;
            background: var(--primary-500);
        }
        .so-card:hover { transform: translateY(-2px); border-color: var(--primary-400); box-shadow: 0 8px 20px -10px rgba(0, 0, 0, .25); }
        .so-card.is-muted::before { background: var(--gray-400); }
        .so-card-label { font-size: 1rem; font-weight: 700; color: var(--gray-900); }
        .so-card-count { font-size: 2.1rem; font-weight: 800; line-height: 1.1; margin-top: .6rem; color: var(--primary-600); font-variant-numeric: tabular-nums; }
        .so-card.is-muted .so-card-count { color: var(--gray-500); }
        .so-card-unit { font-size: .8rem; font-weight: 500; color: var(--gray-500); margin-inline-start: .25rem; }
        .so-card-hint { font-size: .75rem; color: var(--gray-500); margin-top: .35rem; }
        .so-card-arrow { position: absolute; top: 1.1rem; inset-inline-end: 1rem; width: 1.1rem; height: 1.1rem; color: var(--gray-400); }
        .so-card:hover .so-card-arrow { color: var(--primary-500); }

        .so-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .so-table th { text-align: start; font-weight: 600; color: var(--gray-500); padding: .6rem .75rem; border-bottom: 1px solid var(--gray-200); }
        .so-table td { padding: .6rem .75rem; border-bottom: 1px solid var(--gray-100); color: var(--gray-800); }
        .so-table tr:last-child td { border-bottom: 0; }
        .so-num { font-variant-numeric: tabular-nums; }
        .so-empty { text-align: center; padding: 2rem; color: var(--gray-500); }

        .dark .so-card { background: var(--gray-900); border-color: rgba(255, 255, 255, .1); }
        .dark .so-card:hover { border-color: var(--primary-500); }
        .dark .so-card-label { color: #fff; }
        .dark .so-card-count, .dark .so-summary-count { color: var(--primary-400); }
        .dark .so-table th { border-color: rgba(255, 255, 255, .1); color: var(--gray-400); }
        .dark .so-table td { border-color: rgba(255, 255, 255, .05); color: var(--gray-200); }
    </style>

    @php($level = $this->getLevel())

    @if ($level === 'grades')
        <x-filament::section>
            <div class="so-summary">
                <div>
                    <div class="so-summary-count">{{ number_format($this->getTotal()) }}</div>
                    <div class="so-summary-label">إجمالي الطلبة النشطين المقيدين في السنة الحالية — اختر صفاً لعرض شعبه</div>
                </div>
            </div>
        </x-filament::section>

        <div class="so-grid">
            @forelse ($this->getGradeCards() as $card)
                <a href="{{ $card['url'] }}" class="so-card {{ ($card['muted'] ?? false) ? 'is-muted' : '' }}">
                    <x-filament::icon icon="heroicon-m-chevron-left" class="so-card-arrow" />
                    <div class="so-card-label">{{ $card['label'] }}</div>
                    <div class="so-card-count">{{ $card['count'] }}<span class="so-card-unit">طالب</span></div>
                    @if (! empty($card['hint']))
                        <div class="so-card-hint">{{ $card['hint'] }}</div>
                    @endif
                </a>
            @empty
                <div class="so-empty">لا توجد صفوف</div>
            @endforelse
        </div>
    @elseif ($level === 'sections')
        <x-filament::section>
            <div class="so-summary">
                <div>
                    <div class="so-summary-count">{{ $this->getGradeTotal() }}</div>
                    <div class="so-summary-label">طالب نشط مقيد في هذا الصف — اختر شعبة لعرض أسماء الطلبة</div>
                </div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    @if ($listsUrl = $this->getListsUrl())
                        <x-filament::button tag="a" :href="$listsUrl" icon="heroicon-o-queue-list" color="gray">
                            استخراج قوائم الصف
                        </x-filament::button>
                    @endif
                    <x-filament::button tag="a" :href="static::getUrl()" icon="heroicon-o-arrow-uturn-right" color="gray" outlined>
                        رجوع للصفوف
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <div class="so-grid">
            @forelse ($this->getSectionCards() as $card)
                <a href="{{ $card['url'] }}" class="so-card {{ ($card['muted'] ?? false) ? 'is-muted' : '' }}">
                    <x-filament::icon icon="heroicon-m-chevron-left" class="so-card-arrow" />
                    <div class="so-card-label">{{ $card['label'] }}</div>
                    <div class="so-card-count">{{ $card['count'] }}<span class="so-card-unit">طالب</span></div>
                </a>
            @empty
                <div class="so-empty">لا توجد شعب مرتبطة بهذا الصف</div>
            @endforelse
        </div>
    @else
        @php($students = $this->getStudents())

        <x-filament::section>
            <div class="so-summary">
                <div>
                    <div class="so-summary-count">{{ $students->count() }}</div>
                    <div class="so-summary-label">طالب نشط</div>
                </div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    @if ($listsUrl = $this->getListsUrl())
                        <x-filament::button tag="a" :href="$listsUrl" icon="heroicon-o-queue-list" color="gray">
                            استخراج القائمة
                        </x-filament::button>
                    @endif
                    <x-filament::button
                        tag="a"
                        :href="$this->grade === \App\Filament\Pages\StudentsOverview::UNENROLLED ? static::getUrl() : static::getUrl(['grade' => $this->grade])"
                        icon="heroicon-o-arrow-uturn-right"
                        color="gray"
                        outlined
                    >
                        رجوع
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            @if ($students->isEmpty())
                <div class="so-empty">لا يوجد طلبة</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="so-table">
                        <thead>
                            <tr>
                                <th style="width:3rem;">م</th>
                                <th>اسم الطالب</th>
                                <th>رقم الطالب</th>
                                <th>الرقم الوطني</th>
                                <th>ولي الأمر</th>
                                <th>هاتف ولي الأمر</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $i => $student)
                                <tr>
                                    <td class="so-num">{{ $i + 1 }}</td>
                                    <td style="font-weight:600;">{{ $student->full_name }}</td>
                                    <td class="so-num">{{ $student->id }}</td>
                                    <td class="so-num">{{ $student->national_id ?: '—' }}</td>
                                    <td>{{ $student->parent?->name ?: '—' }}</td>
                                    <td class="so-num" dir="ltr" style="text-align:end;">{{ $student->parent?->phone ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
