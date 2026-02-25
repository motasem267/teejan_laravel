<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LoadEmployeePermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()) {
            // Eager load permissions to avoid N+1 queries
            Auth::user()->load('permissions');
        }

        // Log page views for Filament routes (GET requests)
        try {
            if (Auth::check() && $request->isMethod('GET')) {
                $route = $request->route();
                $routeName = $route?->getName() ?? '';

                if ($routeName && Str::startsWith($routeName, 'filament.')) {
                    $params = $route->parameters() ?? [];

                    $modelType = null;
                    $modelId = null;

                    // Filament often provides a 'record' parameter containing the model or id
                    if (isset($params['record'])) {
                        $record = $params['record'];
                        if (is_object($record)) {
                            $modelType = get_class($record);
                            $modelId = $record->getKey();
                        } else {
                            $modelId = $record;
                        }
                    }

                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'view',
                        'description' => 'Viewed route: ' . ($routeName ?: $request->path()),
                        'model_type' => $modelType,
                        'model_id' => $modelId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // do not block request on logging failure
        }

        return $next($request);
    }
}
