<x-filament-widgets::widget>
    {{-- معلومات الموظف --}}
    @if($employee)
    <div style="background: #1f2937; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #374151;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div style="width: 50px; height: 50px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">
                {{ substr($employee->name ?? 'M', 0, 1) }}
            </div>
            <div>
                <h2 style="font-size: 20px; font-weight: bold; margin: 0; color: #f9fafb;">مرحباً، {{ $employee->name ?? 'المستخدم' }}</h2>
                <p style="color: #9ca3af; margin: 5px 0 0 0;">{{ $employee->employee_type_id == 1 ? 'مدير' : 'موظف' }}</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            <div style="background: #111827; padding: 15px; border-radius: 6px; border: 1px solid #374151;">
                <p style="color: #9ca3af; font-size: 13px; margin: 0 0 5px 0;">📧 البريد الإلكتروني</p>
                <p style="font-weight: 600; font-size: 15px; margin: 0; color: #f9fafb;">{{ $employee->email ?? 'غير متوفر' }}</p>
            </div>

            <div style="background: #111827; padding: 15px; border-radius: 6px; border: 1px solid #374151;">
                <p style="color: #9ca3af; font-size: 13px; margin: 0 0 5px 0;">🆔 الرقم الوظيفي</p>
                <p style="font-weight: 600; font-size: 15px; margin: 0; color: #f9fafb;">{{ $employee->id ?? 'غير متوفر' }}</p>
            </div>

            <div style="background: #111827; padding: 15px; border-radius: 6px; border: 1px solid #374151;">
                <p style="color: #9ca3af; font-size: 13px; margin: 0 0 5px 0;">📚 عدد الفصول</p>
                <p style="font-weight: 600; font-size: 15px; margin: 0; color: #f9fafb;">{{ count($teacherClasses) }} فصل</p>
            </div>
        </div>
    </div>

    {{-- جدول الفصول الدراسية --}}
    @if(count($teacherClasses) > 0)
        <div style="background: #1f2937; padding: 20px; border-radius: 8px; border: 1px solid #374151;">
            <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 15px 0; color: #f9fafb;">📚 الفصول التي تقوم بتدريسها</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #111827;">
                        <th style="padding: 12px; text-align: right; font-size: 14px; font-weight: 600; border-bottom: 2px solid #374151; color: #f9fafb;">#</th>
                        <th style="padding: 12px; text-align: right; font-size: 14px; font-weight: 600; border-bottom: 2px solid #374151; color: #f9fafb;">الصف</th>
                        <th style="padding: 12px; text-align: right; font-size: 14px; font-weight: 600; border-bottom: 2px solid #374151; color: #f9fafb;">الشعبة</th>
                        <th style="padding: 12px; text-align: right; font-size: 14px; font-weight: 600; border-bottom: 2px solid #374151; color: #f9fafb;">المادة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teacherClasses as $index => $class)
                        <tr style="border-bottom: 1px solid #374151;">
                            <td style="padding: 12px; text-align: right; font-size: 15px; color: #e5e7eb;">{{ $index + 1 }}</td>
                            <td style="padding: 12px; text-align: right; font-size: 15px; color: #e5e7eb;">{{ $class['grade'] }}</td>
                            <td style="padding: 12px; text-align: right; font-size: 15px; color: #e5e7eb;">{{ $class['section'] }}</td>
                            <td style="padding: 12px; text-align: right; font-size: 15px; color: #e5e7eb;">{{ $class['subject'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="background: #1f2937; padding: 30px; border-radius: 8px; border: 1px solid #374151; text-align: center;">
            <div style="font-size: 60px; margin-bottom: 15px;">📋</div>
            <h3 style="font-size: 20px; font-weight: bold; color: #f9fafb; margin: 0 0 10px 0;">لا توجد بيانات</h3>
            <p style="color: #9ca3af; margin: 0; font-size: 15px;">لم يتم تخصيص أي فصول دراسية حتى الآن.</p>
        </div>
    @endif
    @else
        <div style="background: #1f2937; padding: 40px; border-radius: 8px; border: 1px solid #374151; text-align: center;">
            <div style="font-size: 80px; margin-bottom: 20px;">🔒</div>
            <h3 style="font-size: 22px; font-weight: bold; color: #f9fafb; margin: 0 0 10px 0;">لا توجد بيانات</h3>
            <p style="color: #9ca3af; margin: 0; font-size: 16px;">يرجى تسجيل الدخول لعرض المعلومات.</p>
        </div>
    @endif
</x-filament-widgets::widget>
