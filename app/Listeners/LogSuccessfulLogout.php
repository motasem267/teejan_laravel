<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
	/**
	 * Handle the event.
	 */
	public function handle(Logout $event): void
	{
		try {
			$user = $event->user;
			$id = $user ? $user->getAuthIdentifier() : null;
			$ip = request()?->ip();
			$modelType = $user ? get_class($user) : null;

		

			ActivityLog::create([
				'user_id' => $id,
				'action' => 'logout',
				'description' => 'User logged out: ' . ($user?->email ?? $id),
				'model_type' => $modelType,
				'model_id' => $id,
				'ip_address' => $ip,
				'user_agent' => request()?->userAgent(),
				'created_at' => now(),
			]);
		} catch (\Throwable $e) {
			Log::error('Failed to write ActivityLog for logout: ' . $e->getMessage());
		}
	}
}
