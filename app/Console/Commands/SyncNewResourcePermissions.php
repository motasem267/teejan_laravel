<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncNewResourcePermissions extends Command
{
    protected $signature = 'permissions:sync-new-resources';
    protected $description = 'Sync new resource permissions to all employees';

    public function handle()
    {
        $this->info('Starting to sync new resource permissions...');

        $resources = [
            'annual_subscription_fees',
            'bonuses',
            'bonus_types',
            'deductions',
            'deduction_types',
            'expenses',
            'expenses_types',
            'installments',
            'installment_types',
            'parents',
            'payment_methods',
        ];

        $employees = DB::table('employees')->pluck('id');
        $this->info("Found {$employees->count()} employees");

        $totalAttached = 0;

        foreach ($resources as $resource) {
            $permissionIds = DB::table('permissions')
                ->where('name', 'LIKE', $resource . '%')
                ->pluck('id');

            if ($permissionIds->isEmpty()) {
                $this->warn("No permissions found for resource: {$resource}");
                continue;
            }

            $this->info("Processing {$resource}: {$permissionIds->count()} permissions");

            foreach ($employees as $employeeId) {
                foreach ($permissionIds as $permissionId) {
                    $exists = DB::table('employee_permissions')
                        ->where('employee_id', $employeeId)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$exists) {
                        DB::table('employee_permissions')->insert([
                            'employee_id' => $employeeId,
                            'permission_id' => $permissionId,
                        ]);
                        $totalAttached++;
                    }
                }
            }
        }

        $this->info("✅ Done! Attached {$totalAttached} permission entries");

        // Clear cache
        $this->call('cache:clear');
        $this->info('Cache cleared');

        return 0;
    }
}
