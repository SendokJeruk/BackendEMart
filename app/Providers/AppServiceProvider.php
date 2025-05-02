<?php

namespace App\Providers;

use Midtrans\Config;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
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
        $db = Setting::whereIn('name', [
            'MIDTRANS_SERVER_KEY',
            'MIDTRANS_IS_PRODUCTION',
            'MIDTRANS_CLIENT_KEY',
        ])->pluck('value', 'name');

        config()->set('midtrans.server_key',   $db['MIDTRANS_SERVER_KEY'] ?? null);
        config()->set('midtrans.is_production', $db['MIDTRANS_IS_PRODUCTION'] ?? false);

        Config::$serverKey   = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds       = true;
    }
}
