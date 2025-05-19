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
        try {
            $db = Setting::whereIn('name', [
                'MIDTRANS_SERVER_KEY',
                'MIDTRANS_IS_PRODUCTION',
                'MIDTRANS_CLIENT_KEY',
            ])->pluck('value', 'name');

            $serverKey       = $db['MIDTRANS_SERVER_KEY'] ?? null;
            $isProductionRaw = $db['MIDTRANS_IS_PRODUCTION'] ?? false;

            config()->set('midtrans.server_key', $serverKey);
            config()->set('midtrans.is_production', filter_var($isProductionRaw, FILTER_VALIDATE_BOOLEAN));

            Config::$serverKey    = config('midtrans.server_key') ?? '';
            Config::$isProduction = config('midtrans.is_production') ?? false;
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            if (empty($serverKey)) {
                Log::warning('MIDTRANS_SERVER_KEY is not set in settings table.');
            }

        } catch (\Throwable $e) {
            Log::error('Error initializing Midtrans configuration: ' . $e->getMessage());
        }
    }
}
