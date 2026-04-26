<x-filament-panels::page>
    <style>
        .filament-tables-container {
            width: 100% !important;
        }
        
        .filament-tables-container table {
            width: 100% !important;
            table-layout: auto;
        }
        
        .filament-tables-container th,
        .filament-tables-container td {
            text-align: center !important;
        }
    </style>
    
    <div class="w-full">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
