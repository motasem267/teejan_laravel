<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if(!empty($students))
            <div style="margin-top: 30px;">
                <div style="background: #1f2937; padding: 20px; border-radius: 8px; border: 1px solid #374151;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: bold; margin: 0; color: #f9fafb;">جدول الدرجات</h3>
                        @if($fullMark)
                            <span style="background: #111827; color: #93c5fd; padding: 6px 14px; border-radius: 999px; font-size: 14px; border: 1px solid #374151;">
                                الدرجة الكبرى: {{ $fullMark }}
                            </span>
                        @endif
                    </div>

                    @if(!$fullMark)
                        <div style="background: #78350f; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                            ⚠️ لم يتم تحديد الدرجة الكبرى لهذه المادة في هذا الصف والفترة. حدّدها من شاشة "الدرجة الكبرى للمواد" قبل إدخال الدرجات.
                        </div>
                    @endif

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #111827;">
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 220px;">
                                        اسم الطالب
                                    </th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; width: 160px;">
                                        الدرجة
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr style="border-bottom: 1px solid #374151;">
                                        <td style="padding: 12px; text-align: right; font-weight: 500; color: #e5e7eb; border: 1px solid #374151;">
                                            {{ $student['full_name'] }}
                                        </td>
                                        <td style="padding: 8px; border: 1px solid #374151;">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="{{ $fullMark }}"
                                                {{ !$fullMark ? 'disabled' : '' }}
                                                wire:model="studentMarks.{{ $student['enrollment_id'] }}"
                                                style="width: 100%; padding: 8px; background: #111827; color: #f9fafb; border: 1px solid #374151; border-radius: 6px; font-size: 14px;"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <x-filament::button type="submit" size="lg" :disabled="!$fullMark">
                    💾 حفظ الدرجات
                </x-filament::button>
            </div>
        @else
            <div style="background: #78350f; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                ⚠️ يرجى اختيار الفصل والمادة والفترة لعرض جدول الدرجات
            </div>
        @endif
    </form>
</x-filament-panels::page>
