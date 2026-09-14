<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateRequest;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        // ngambil data pengaturan sistem (kayak API keys) yang ada di database
        $keys = Setting::filter($request)->get();
        return response()->json([
            'status' => 'Success',
            'message' => 'Settings data retrieved successfully',
            'data' => $keys
        ]);
    }

    public function update(UpdateRequest $request)
    {
        // ngelooping inputan setting, kalo ada isinya bakal di-insert atau di-update ke tabel settings
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
}
