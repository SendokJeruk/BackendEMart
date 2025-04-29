<?php

use App\Models\Setting;

return [
    'shipping_key' => Setting::getValue('RAJAONGKIR_SHIPPING_KEY'),
    'delivery_key' => Setting::getValue('RAJAONGKIR_DELIVERY_KEY'),
];
// return [
//     'shipping_key' => env('RAJAONGKIR_SHIPPING_KEY'),
//     'delivery_key' => env('RAJAONGKIR_DELIVERY_KEY'),
// ];
