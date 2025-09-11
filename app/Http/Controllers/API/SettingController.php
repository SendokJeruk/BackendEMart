<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index(Request $request)
    {

            $keys = Setting::filter($request)->get();
            return response()->json([
                'status' => 'Success',
                'message' => 'Settings data retrieved successfully',
                'data' => $keys
            ]);

    }
    public function update(Request $request)
    {

            $request->validate([
                'MIDTRANS_SERVER_KEY' => 'nullable|string',
                'MIDTRANS_CLIENT_KEY' => 'nullable|string',
                'MIDTRANS_IS_PRODUCTION' => 'nullable|string',
                'RAJAONGKIR_SHIPPING_KEY' => 'nullable|string',
                'RAJAONGKIR_DELIVERY_KEY' => 'nullable|string',
            ]);

            $settings = [
                'MIDTRANS_SERVER_KEY',
                'MIDTRANS_CLIENT_KEY',
                'MIDTRANS_IS_PRODUCTION',
                'RAJAONGKIR_SHIPPING_KEY',
                'RAJAONGKIR_DELIVERY_KEY'
            ];

            foreach ($settings as $setting) {
                $value = $request->input($setting);

                if (!is_null($value)) {
                    Setting::updateOrCreate(
                        ['name' => $setting],
                        ['value' => $value]
                    );
                }
            }

            return response()->json([
                'status' => 'Success',
                'message' => 'Settings updated successfully'
            ]);

    }

    // public function test()
    // {
    //     return Setting::getValue('MIDTRANS_SERVER_KEY');
    //     return Setting::where('name', 'MIDTRANS_SERVER_KEY')->first()->value;
    // }
}
