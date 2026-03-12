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
            $settings = Setting::whereIn('name', [
                'MIDTRANS_SERVER_KEY',
                'MIDTRANS_IS_PRODUCTION',
                'MIDTRANS_CLIENT_KEY',
                'RAJAONGKIR_SHIPPING_KEY',
                'RAJAONGKIR_DELIVERY_KEY',
            ])->pluck('value', 'name');

            $serverKey = $settings['MIDTRANS_SERVER_KEY'] ?? null;
            $clientKey = $settings['MIDTRANS_CLIENT_KEY'] ?? null;
            $isProductionRaw = $settings['MIDTRANS_IS_PRODUCTION'] ?? false;

            $rajaOngkirShippingKey = $settings['RAJAONGKIR_SHIPPING_KEY'] ?? null;
            $rajaOngkirDeliveryKey = $settings['RAJAONGKIR_DELIVERY_KEY'] ?? null;

            config()->set('midtrans.server_key', $serverKey);
            config()->set('midtrans.client_key', $clientKey);
            config()->set('midtrans.is_production', filter_var($isProductionRaw, FILTER_VALIDATE_BOOLEAN));

            config()->set('rajaongkir.shipping_key', $rajaOngkirShippingKey);
            config()->set('rajaongkir.delivery_key', $rajaOngkirDeliveryKey);

            Config::$serverKey = config('midtrans.server_key') ?? '';
            Config::$isProduction = config('midtrans.is_production') ?? false;
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $keysToCheck = [
                'MIDTRANS_SERVER_KEY' => $serverKey,
                'MIDTRANS_CLIENT_KEY' => $clientKey,
                'RAJAONGKIR_SHIPPING_KEY' => $rajaOngkirShippingKey,
                'RAJAONGKIR_DELIVERY_KEY' => $rajaOngkirDeliveryKey,
            ];

            foreach ($keysToCheck as $name => $value) {
                if (empty($value)) {
                    Log::warning("$name is not set in settings table.");
                }
            }

        } catch (\Throwable $e) {
            Log::error('Error initializing configuration: ' . $e->getMessage());
        }
    }
}
