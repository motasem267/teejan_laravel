<x-filament-panels::page>
    <div class="space-y-6">
        {{-- بطاقة معلومات الموظف --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($employee->name ?? 'M', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            مرحباً، {{ $employee->name ?? 'المستخدم' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            معلم
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">البريد الإلكتروني</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->email ?? 'غير متوفر' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">الرقم الوظيفي</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->id ?? 'غير متوفر' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">عدد الفصول</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ count($teacherClasses) }} فصل
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول الفصول الدراسية --}}
        @if(count($teacherClasses) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                        الفصول التي تقوم بتدريسها
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-gray-700 dark:text-gray-300">#</th>
                                    <th class="px-6 py-3 text-gray-700 dark:text-gray-300">الصف</th>
                                    <th class="px-6 py-3 text-gray-700 dark:text-gray-300">الشعبة</th>
                                    <th class="px-6 py-3 text-gray-700 dark:text-gray-300">المادة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teacherClasses as $index => $class)
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                                            {{ $class['grade'] }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                                            {{ $class['section'] }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                                            {{ $class['subject'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">لا توجد فصول دراسية</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">لم يتم تخصيص أي فصول دراسية لك حتى الآن.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
