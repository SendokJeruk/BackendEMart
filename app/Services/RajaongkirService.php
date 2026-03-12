<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected $shippingKey;
    protected $deliveryKey;
    // ! DISABLE SEMENTARA
    public function __construct()
    {
        $this->shippingKey = config('rajaongkir.shipping_key');
        $this->deliveryKey = config('rajaongkir.delivery_key');
    }

    public function getDomestic($domestic)
    {
        $response = Http::withHeaders([
            'key' => $this->shippingKey,
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search={$domestic}");

        return $response->json();
    }

    public function getCost($origin, $destination, $weight, $courier, $price)
    {

        $response = Http::withHeaders([
            'key' => $this->shippingKey,
        ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => $price,
                ]);

        return $response->json();
    }

    public function trackShipment($awb, $courier, $lastPhone = null)
    {
        $data = [
            'awb' => trim($awb),
            'courier' => strtolower(trim($courier)),
        ];

        if ($courier === 'jne' && $lastPhone) {
            $data['last_phone_number'] = $lastPhone;
        }

        $response = Http::withHeaders([
            'key' => trim($this->shippingKey),
            'accept' => 'application/json',
        ])
        ->withQueryParameters($data)
        ->post('https://rajaongkir.komerce.id/api/v1/track/waybill');

        return $response->json();
    }

}
