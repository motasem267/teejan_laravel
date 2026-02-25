<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeInfoWidget extends Widget
{
    protected string $view = 'filament.widgets.employee-info-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public $employee;
    public $teacherClasses = [];
    
    public function mount(): void
    {
        $this->employee = Auth::user();
        
        if ($this->employee) {
            try {
                // Check if teacher_classes table exists
                if (\Illuminate\Support\Facades\Schema::hasTable('teacher_classes')) {
                    $this->teacherClasses = \App\Models\TeacherClass::where('teacher_id', $this->employee->id)
                        ->with(['classModel.grade', 'classModel.section', 'subject'])
                        ->get()
                        ->map(function ($teacherClass) {
                            return [
                                'grade' => $teacherClass->classModel->grade->name ?? 'غير محدد',
                                'section' => $teacherClass->classModel->section->name ?? 'غير محدد',
                                'subject' => $teacherClass->subject->name ?? 'غير محدد',
                            ];
                        })
                        ->toArray();
                }
            } catch (\Exception $e) {
                // If table doesn't exist or any error, just show empty
                $this->teacherClasses = [];
            }
        }
    }
}
