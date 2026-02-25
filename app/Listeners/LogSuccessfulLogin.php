<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
	/**
	 * Handle the event.
	 */
	public function handle(Login $event): void
	{
		try {
			$user = $event->user;
			$id = $user ? $user->getAuthIdentifier() : null;


			ActivityLog::create([
				'user_id' => $id,
				'action' => 'login',
				'description' => 'User logged in: ' . ($user?->email ?? $id),
				'model_type' => $user ? get_class($user) : null,
				'model_id' => $id,
				'ip_address' => request()?->ip(),
				'user_agent' => request()?->userAgent(),
				'created_at' => now(),
			]);
		} catch (\Throwable $e) {
			Log::error('Failed to write ActivityLog for login: ' . $e->getMessage());
		}
	}
}
