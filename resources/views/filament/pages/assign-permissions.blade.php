<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                <x-filament::loading-indicator wire:loading wire:target="save" class="h-5 w-5" />
                <span wire:loading.remove wire:target="save">حفظ الصلاحيات</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
