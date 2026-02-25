<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button
                wire:click="exportPdf"
                icon="heroicon-o-arrow-down-tray"
                size="lg"
                color="primary"
            >
                تصدير PDF
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
