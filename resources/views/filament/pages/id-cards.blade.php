<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button
                wire:click="generate"
                wire:loading.attr="disabled"
                wire:target="generate"
                icon="heroicon-o-identification"
                size="lg"
                color="primary"
            >
                <span wire:loading.remove wire:target="generate">إصدار البطاقات (PDF)</span>
                <span wire:loading wire:target="generate">جاري تجهيز البطاقات...</span>
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
