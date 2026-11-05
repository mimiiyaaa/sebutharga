<?php

namespace App\Providers;

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
        $this->app->resolving('dompdf.wrapper', function ($pdf) {
            $pdf->setCallbacks([[
                'event' => 'begin_frame',
                'f' => [\App\Services\PdfSignatureLayout::class, 'alignToBottom'],
            ]]);
        });
    }
}
