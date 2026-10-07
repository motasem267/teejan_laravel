<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button
                wire:click="generate"
                wire:loading.attr="disabled"
                wire:target="generate"
                icon="heroicon-o-arrow-down-tray"
                size="lg"
                color="primary"
            >
                <span wire:loading.remove wire:target="generate">استخراج القائمة</span>
                <span wire:loading wire:target="generate">جاري تجهيز القائمة...</span>
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
