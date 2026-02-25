<x-filament-panels::page>
    <div class="mb-4">
        <div class="max-w-xs">
            <select 
                wire:model.live="selectedMonth"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 px-3 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200 hover:border-gray-400 hover:bg-gray-100"
                style="color: #f9fafb !important;"
            >
                <option value="1" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">يناير</option>
                <option value="2" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">فبراير</option>
                <option value="3" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">مارس</option>
                <option value="4" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">أبريل</option>
                <option value="5" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">مايو</option>
                <option value="6" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">يونيو</option>
                <option value="7" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">يوليو</option>
                <option value="8" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">أغسطس</option>
                <option value="9" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">سبتمبر</option>
                <option value="10" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">أكتوبر</option>
                <option value="11" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">نوفمبر</option>
                <option value="12" style="background-color: #f9fafb; color: #1f2937; padding: 8px;">ديسمبر</option>
            </select>
        </div>
    </div>
    {{ $this->table }}
</x-filament-panels::page>
