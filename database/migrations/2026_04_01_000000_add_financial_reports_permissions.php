<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add financial report permissions
        $permissions = [
            ['name' => 'expenses-report.view', 'guard_name' => 'web'],
            ['name' => 'salaries-report.view', 'guard_name' => 'web'],
            ['name' => 'revenue-report.view', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission);
        }

        // Grant permissions to admin role
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            foreach (['expenses-report.view', 'salaries-report.view', 'revenue-report.view'] as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if ($permission) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $permission->id,
                        'role_id' => $adminRole->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'expenses-report.view',
            'salaries-report.view', 
            'revenue-report.view',
        ])->delete();
    }
};
