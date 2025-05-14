<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class TimezoneServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set the default timezone for PHP
        date_default_timezone_set(config('app.timezone'));
        
        // Set the locale for Carbon
        Carbon::setLocale(config('app.locale'));
    }
}