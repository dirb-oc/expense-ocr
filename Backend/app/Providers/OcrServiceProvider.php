<?php

namespace App\Providers;

use App\Services\OCR\OcrService;
use App\Services\OCR\TesseractOcrService;
use Illuminate\Support\ServiceProvider;

class OcrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            OcrService::class,
            TesseractOcrService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}