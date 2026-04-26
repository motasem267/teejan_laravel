<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add attendance report permissions only (other report permissions already exist)
        $permissions = [
            ['name' => 'attendance-report', 'label' => 'تقرير الحضور', 'parent_id' => null],
            ['name' => 'attendance-report.view', 'label' => 'عرض تقرير الحضور', 'parent_id' => null],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission);
        }

        // Set parent permissions for attendance report
        $attendanceReportParent = DB::table('permissions')->where('name', 'attendance-report')->first();
        if ($attendanceReportParent) {
            DB::table('permissions')
                ->where('name', 'attendance-report.view')
                ->update(['parent_id' => $attendanceReportParent->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'attendance-report',
            'attendance-report.view',
        ])->delete();
    }
};
