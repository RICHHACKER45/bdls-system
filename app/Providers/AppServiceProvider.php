<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        // Detect if the request is coming through an ngrok tunnel
        if (str_contains(request()->getHost(), 'ngrok-free.app') ||
            request()->header('X-Forwarded-Proto') === 'https') {

            URL::forceScheme('https');
        }
    }
}
