<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-4">
            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                color="success"
                size="lg"
            >
                <x-filament::loading-indicator wire:loading wire:target="create" class="h-5 w-5" />
                <span wire:loading.remove wire:target="create">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5 inline-block mr-2" />
                    إنشاء القيود للطلبة المحددين
                </span>
                <span wire:loading wire:target="create">جاري إنشاء القيود...</span>
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\StudentEnrollments\StudentEnrollmentResource::getUrl('index') }}"
                color="gray"
                size="lg"
            >
                العودة للقائمة
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
