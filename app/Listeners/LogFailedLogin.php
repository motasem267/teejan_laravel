<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
	/**
	 * Handle the event.
	 */
	public function handle(Failed $event): void
	{
		try {
			$credentials = $event->credentials ?? [];
			$email = is_array($credentials) && isset($credentials['email']) ? $credentials['email'] : null;
			$ip = request()?->ip();
            
			ActivityLog::create([
				'user_id' => null,
				'action' => 'login_failed',
				'description' => 'Failed login: ' . ($email ?? json_encode($credentials)),
				'model_type' => null,
				'model_id' => null,
				'ip_address' => $ip,
				'user_agent' => request()?->userAgent(),
				'created_at' => now(),
			]);
		} catch (\Throwable $e) {
			Log::error('Failed to write ActivityLog for failed login: ' . $e->getMessage());
		}
	}
}
