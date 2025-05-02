<?php

namespace App\Providers;

use Midtrans\Config;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class MidtransConfigServiceProvider extends ServiceProvider
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
    public function boot()
    {
        // $serverKey = Setting::getValue('MIDTRANS_SERVER_KEY');
        // $clientKey = Setting::getValue('MIDTRANS_CLIENT_KEY');
        // $isProduction = Setting::getValue('MIDTRANS_IS_PRODUCTION');

        // Config::$serverKey = $serverKey;
        // Config::$clientKey = $clientKey;
        // Config::$isProduction = $isProduction;
        // Config::$isSanitized = true;
        // Config::$is3ds = true;
    }
}
