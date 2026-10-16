<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (app()->environment('production') ||
            str_contains(config('app.url'), 'https://') ||
            request()->header('X-Forwarded-Proto') === 'https' ||
            str_contains(request()->getHost(), 'ngrok-free.app')) {

            URL::forceScheme('https');
        }
    }
}
