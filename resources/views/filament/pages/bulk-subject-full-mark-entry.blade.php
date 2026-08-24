<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if(!empty($subjects))
            <div style="margin-top: 30px;">
                <div style="background: #1f2937; padding: 20px; border-radius: 8px; border: 1px solid #374151;">
                    <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 20px 0; color: #f9fafb;">الدرجة الكبرى لكل مادة</h3>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #111827;">
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 220px;">
                                        المادة
                                    </th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; width: 160px;">
                                        الدرجة الكبرى
                                    </th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; width: 100px;">
                                        الحالة
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subj)
                                    <tr style="border-bottom: 1px solid #374151;">
                                        <td style="padding: 12px; text-align: right; font-weight: 500; color: #e5e7eb; border: 1px solid #374151;">
                                            {{ $subj['name'] }}
                                        </td>
                                        <td style="padding: 8px; border: 1px solid #374151;">
                                            <input
                                                type="number"
                                                step="1"
                                                min="1"
                                                max="1000"
                                                wire:model="fullMarks.{{ $subj['id'] }}"
                                                style="width: 100%; padding: 8px; background: #111827; color: #f9fafb; border: 1px solid #374151; border-radius: 6px; font-size: 14px;"
                                            />
                                        </td>
                                        <td style="padding: 8px; text-align: center; border: 1px solid #374151;">
                                            @if(in_array($subj['id'], $existingSubjectIds))
                                                <span style="background: #164e63; color: #a5f3fc; padding: 4px 10px; border-radius: 999px; font-size: 12px;">معدَّل</span>
                                            @else
                                                <span style="background: #14532d; color: #bbf7d0; padding: 4px 10px; border-radius: 999px; font-size: 12px;">جديد</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <x-filament::button type="submit" size="lg">
                    💾 حفظ الدرجات الكبرى
                </x-filament::button>
            </div>
        @else
            <div style="background: #78350f; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                ⚠️ يرجى اختيار الصف الدراسي والفترة لعرض قائمة المواد
            </div>
        @endif
    </form>
</x-filament-panels::page>
