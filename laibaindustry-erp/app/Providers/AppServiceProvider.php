<?php

namespace App\Providers;

use App\Models\Currency;
use App\Models\InternationalPurchaseOrder;
use App\Support\Schema\InternationalPayablesSchema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
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
        Route::bind('international_purchase', function (string $value) {
            return InternationalPurchaseOrder::findOrFail($value);
        });

        try {
            if (! app()->isProduction()) {
                InternationalPayablesSchema::ensureTableExists();
            }
        } catch (\Throwable) {
            // DB unavailable or not migrated yet
        }

        try {
            $default = Currency::query()->where('is_default', true)->first();
            View::share('currencySymbol', $default?->symbol ?? '$');
            View::share('currencyCode', $default?->code ?? 'USD');
        } catch (\Throwable $e) {
            View::share('currencySymbol', '$');
            View::share('currencyCode', 'USD');
        }

        Blade::directive('displayDate', function ($expression) {
            return "<?php echo e(format_display_date($expression)); ?>";
        });

        Blade::directive('displayDateTime', function ($expression) {
            return "<?php echo e(format_display_datetime($expression)); ?>";
        });
    }
}
