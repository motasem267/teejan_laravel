<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class CheckUserPermissions extends Command
{
    protected $signature = 'permissions:check {email}';
    protected $description = 'Check permissions for a user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = Employee::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->info("Email: {$user->email}");
        $this->line('');

        $resources = [
            'bonuses',
            'annual_subscription_fees',
            'expenses',
            'parents',
        ];

        foreach ($resources as $resource) {
            $this->info("--- {$resource} ---");
            $this->line("View:   " . ($user->hasPermission("{$resource}.view") ? '✅ YES' : '❌ NO'));
            $this->line("Create: " . ($user->hasPermission("{$resource}.create") ? '✅ YES' : '❌ NO'));
            $this->line("Edit:   " . ($user->hasPermission("{$resource}.edit") ? '✅ YES' : '❌ NO'));
            $this->line("Delete: " . ($user->hasPermission("{$resource}.delete") ? '✅ YES' : '❌ NO'));
            $this->line('');
        }

        return 0;
    }
}
