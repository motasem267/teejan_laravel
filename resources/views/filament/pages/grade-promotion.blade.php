<x-filament-panels::page>
    <form wire:submit="promote">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" wire:loading.attr="disabled" color="success" size="lg">
                <x-filament::loading-indicator wire:loading wire:target="promote" class="h-5 w-5" />
                <span wire:loading.remove wire:target="promote">
                    <x-filament::icon icon="heroicon-o-arrow-up-circle" class="w-5 h-5 inline-block mr-2" />
                    ترحيل الطلبة المحددين للصف التالي
                </span>
                <span wire:loading wire:target="promote">جاري الترحيل...</span>
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
