<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            @if($pendingDeleteCount)
                <x-filament::button type="button" wire:click="confirmAndSave" color="danger" size="lg">
                    ⚠️ تأكيد الحذف والحفظ
                </x-filament::button>
            @else
                <x-filament::button type="submit" size="lg">
                    💾 حفظ التخصيصات
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament-panels::page>
