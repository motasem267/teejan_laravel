<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if(!empty($students) && !empty($questions))
            <div style="margin-top: 30px;">
                <div style="background: #1f2937; padding: 20px; border-radius: 8px; border: 1px solid #374151;">
                    <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 20px 0; color: #f9fafb;">جدول التقييم</h3>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #111827;">
                                    <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 200px;">
                                        اسم الطالب
                                    </th>
                                    @foreach($questions as $question)
                                        <th style="padding: 12px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 180px; max-width: 220px; white-space: normal; word-break: break-word;">
                                            {{ $question['label'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr style="border-bottom: 1px solid #374151;">
                                        <td style="padding: 12px; text-align: right; font-weight: 500; color: #e5e7eb; border: 1px solid #374151;">
                                            {{ $student['full_name'] }}
                                        </td>
                                        @foreach($questions as $question)
                                            <td style="padding: 8px; border: 1px solid #374151;">
                                                <select 
                                                    wire:model="studentAnswers.{{ $student['id'] }}.{{ $question['id'] }}"
                                                    style="width: 100%; padding: 8px; background: #111827; color: #f9fafb; border: 1px solid #374151; border-radius: 6px; font-size: 14px;"
                                                >
                                                    <option value="">-- اختر --</option>
                                                    @foreach($answers[$question['id']] ?? [] as $answerId => $answerLabel)
                                                        <option value="{{ $answerId }}">{{ $answerLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div style="background: #78350f; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                ⚠️ يرجى اختيار الصف الدراسي والمادة لعرض جدول التقييم
            </div>
        @endif

        @if(!empty($students) && !empty($questions))
            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <x-filament::button
                    type="submit"
                    size="lg"
                >
                    💾 حفظ التقييمات
                </x-filament::button>
            </div>
        @endif
    </form>
</x-filament-panels::page>
