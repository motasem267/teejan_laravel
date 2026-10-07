<x-filament-panels::page>
    <form wire:submit="create" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button type="submit" icon="heroicon-o-check" size="lg" wire:loading.attr="disabled" wire:target="create">
                <span wire:loading.remove wire:target="create">قيد الموظفين المحددين</span>
                <span wire:loading wire:target="create">جاري القيد...</span>
            </x-filament::button>

            <x-filament::button tag="a" :href="\App\Filament\Resources\EmployeeEnrollments\EmployeeEnrollmentResource::getUrl('index')" color="gray" outlined>
                إلغاء
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
