@php
    $activeYear = \App\Models\academic_years::getActiveLabel();
@endphp

@auth
    <div style="display:flex; align-items:center; margin-inline-end:.5rem;" title="كل الصفحات تعرض بيانات السنة الدراسية الفعالة افتراضياً">
        @if ($activeYear)
            <x-filament::badge color="primary" icon="heroicon-m-calendar-days" size="lg">
                العام الدراسي {{ $activeYear }}
            </x-filament::badge>
        @else
            <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle" size="lg">
                لا توجد سنة دراسية فعالة
            </x-filament::badge>
        @endif
    </div>
@endauth
