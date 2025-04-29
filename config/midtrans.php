<?php

use App\Models\Setting;

return [
    'server_key' => Setting::getValue('MIDTRANS_SERVER_KEY'),
    'client_key' => Setting::getValue('MIDTRANS_CLIENT_KEY'),
    'is_production' => Setting::getValue('MIDTRANS_IS_PRODUCTION'),
];
// return [
//     'server_key' => env('MIDTRANS_SERVER_KEY'),
//     'client_key' => env('MIDTRANS_CLIENT_KEY'),
//     'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
// ];
