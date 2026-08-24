<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if($hasNoAssignments)
            <div style="background: #78350f; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                ⚠️ هذا الفصل ليس له معلمون/مواد مخصصة بعد لهذه السنة الدراسية.
                يرجى تخصيص المعلمين أولاً من شاشة
                <a href="{{ \App\Filament\Pages\BulkTeacherClassAssignment::getUrl() }}" style="text-decoration: underline; color: #fde68a;">
                    "تخصيص معلم لعدة فصول"
                </a>
                قبل بناء الجدول الأسبوعي.
            </div>
        @elseif(!empty($days) && !empty($lessonTimes))
            <div style="margin-top: 30px;">
                <div style="background: #1f2937; padding: 20px; border-radius: 8px; border: 1px solid #374151;">
                    <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 20px 0; color: #f9fafb;">الجدول الأسبوعي</h3>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="background: #111827;">
                                    <th style="padding: 10px; text-align: right; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 140px;">
                                        الحصة
                                    </th>
                                    @foreach($days as $day)
                                        <th style="padding: 10px; text-align: center; font-weight: 600; color: #f9fafb; border: 1px solid #374151; min-width: 160px;">
                                            {{ $day['day_name_ar'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lessonTimes as $lt)
                                    @if($lt['is_break'])
                                        <tr style="background: #111827;">
                                            <td colspan="{{ count($days) + 1 }}" style="padding: 6px 12px; text-align: center; color: #9ca3af; font-size: 12px; border: 1px solid #374151;">
                                                استراحة ({{ $lt['start_time'] }} - {{ $lt['end_time'] }})
                                            </td>
                                        </tr>
                                    @else
                                        <tr style="border-bottom: 1px solid #374151;">
                                            <td style="padding: 8px 12px; text-align: right; font-weight: 500; color: #e5e7eb; border: 1px solid #374151;">
                                                الحصة {{ $lt['period_number'] }}<br>
                                                <span style="font-size: 11px; color: #9ca3af;">{{ $lt['start_time'] }} - {{ $lt['end_time'] }}</span>
                                            </td>
                                            @foreach($days as $day)
                                                <td style="padding: 6px; border: 1px solid #374151;">
                                                    <select
                                                        wire:model="scheduleGrid.{{ $day['id'] }}.{{ $lt['id'] }}"
                                                        style="width: 100%; padding: 6px; background: #111827; color: #f9fafb; border: 1px solid #374151; border-radius: 6px; font-size: 12px;"
                                                    >
                                                        <option value="">-- فارغ --</option>
                                                        @foreach($teacherClassOptions as $tcId => $label)
                                                            <option value="{{ $tcId }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <x-filament::button type="submit" size="lg">
                    💾 حفظ الجدول الأسبوعي
                </x-filament::button>
            </div>
        @else
            <div style="background: #78350f; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #f59e0b; color: #fef3c7;">
                ⚠️ يرجى اختيار الفصل لعرض الجدول الأسبوعي
            </div>
        @endif
    </form>
</x-filament-panels::page>
