<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

class ActivityObserver
{
    protected function shouldIgnore(Model $model): bool
    {
        if (property_exists($model, 'disableActivityLog') && $model->disableActivityLog) {
            return true;
        }

        return false;
    }

    protected function log(string $action, Model $model): void
    {
        if ($this->shouldIgnore($model)) {
            return;
        }

        // If the model being logged is ActivityLog itself, write a lightweight entry
        // to the Laravel log to avoid recursion while still recording the event.
        if ($model instanceof ActivityLog) {
            try {
                $request = request();
                Log::info('ActivityLog created', [
                    'user_id' => Auth::id(),
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'ip' => $request?->ip(),
                    'ua' => $request?->userAgent(),
                ]);
            } catch (\Throwable $e) {
                // ignore
            }

            return;
        }

        try {
            $request = request();
            $ip = $request?->ip();
            $ua = $request?->userAgent();

            $modelType = get_class($model);
            $modelId = $model->getKey();

            $description = ucfirst($action) . ' ' . class_basename($model) . ' #' . ($modelId ?? 'n/a');

            if ($action === 'updated') {
                $changes = $model->getChanges();
                if (!empty($changes)) {
                    $description .= ' (changed: ' . implode(', ', array_keys($changes)) . ')';
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'ip_address' => $ip,
                'user_agent' => $ua,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Don't let logging break application flow.
        }
    }

    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }
}
