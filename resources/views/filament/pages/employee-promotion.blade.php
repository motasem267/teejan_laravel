<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button
                wire:click="promote"
                wire:confirm="متأكد من ترحيل الموظفين المحددين للسنة الجديدة؟"
                wire:loading.attr="disabled"
                wire:target="promote"
                icon="heroicon-o-arrow-uturn-left"
                size="lg"
            >
                <span wire:loading.remove wire:target="promote">ترحيل الموظفين المحددين</span>
                <span wire:loading wire:target="promote">جاري الترحيل...</span>
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
