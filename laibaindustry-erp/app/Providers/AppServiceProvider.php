<?php

namespace App\Providers;

use App\Models\Currency;
use Illuminate\Support\Facades\View;
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
        try {
            $default = Currency::query()->where('is_default', true)->first();
            View::share('currencySymbol', $default?->symbol ?? '$');
            View::share('currencyCode', $default?->code ?? 'USD');
        } catch (\Throwable $e) {
            View::share('currencySymbol', '$');
            View::share('currencyCode', 'USD');
        }
    }
}
