<?php
// app/Providers/PdfServiceProvider.php

namespace App\Providers;

use Barryvdh\DomPDF\ServiceProvider as DomPDFServiceProvider;
use Illuminate\Support\ServiceProvider;
use PDF;

class PdfServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the DomPDF package
        $this->app->register(DomPDFServiceProvider::class);
        
        // Don't use configure() method as it's not available in this Laravel version
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Additional PDF configuration if needed
    }
}