<?php

namespace App\Providers;

use App\Listeners\LogFailedLogin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ActivityObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

         if (app()->environment('production') || env('APP_ENV') === 'production')
           { URL::forceRootUrl(config('app.url'));
             URL::forceScheme('https');
         }



        // Automatically register ActivityObserver for all Eloquent models in app/Models
        $modelsPath = app_path('Models');

        if (is_dir($modelsPath)) {
            $files = scandir($modelsPath);

            foreach ($files as $file) {
                if (!str_ends_with($file, '.php')) {
                    continue;
                }

                $class = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);

                if (!class_exists($class)) {
                    // let Composer autoload attempt; if it fails, skip
                    continue;
                }

                try {
                    $ref = new \ReflectionClass($class);
                    if ($ref->isInstantiable() && $ref->isSubclassOf(Model::class)) {
                        $class::observe(ActivityObserver::class);
                    }
                } catch (\Throwable $e) {
                    // ignore model registration failures
                }
            }
        }

        // Register authentication event listeners (login)
        try {
            Event::listen(Login::class, [LogSuccessfulLogin::class, 'handle']);
        } catch (\Throwable $e) {
            // ignore event registration errors
        }

         try {
            Event::listen(\Illuminate\Auth\Events\Logout::class, [\App\Listeners\LogSuccessfulLogout::class, 'handle']);
        } catch (\Throwable $e) {
            // ignore event registration errors
        }

         try {
            Event::listen(\Illuminate\Auth\Events\Failed::class, [LogFailedLogin::class, 'handle']);
        } catch (\Throwable $e) {
            // ignore event registration errors
        }
    }
}
