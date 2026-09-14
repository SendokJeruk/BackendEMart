<?php
namespace App\Http\Requests\Setting;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'MIDTRANS_SERVER_KEY' => 'nullable|string',
            'MIDTRANS_CLIENT_KEY' => 'nullable|string',
            'MIDTRANS_IS_PRODUCTION' => 'nullable|string',
            'RAJAONGKIR_SHIPPING_KEY' => 'nullable|string',
            'RAJAONGKIR_DELIVERY_KEY' => 'nullable|string',
        ];
    }
        public function attributes() {
        return [
            'MIDTRANS_SERVER_KEY' => 'Server Key Midtrans',
            'MIDTRANS_CLIENT_KEY' => 'Client Key Midtrans',
            'MIDTRANS_IS_PRODUCTION' => 'Status Produksi Midtrans',
            'RAJAONGKIR_SHIPPING_KEY' => 'API Key RajaOngkir (Shipping)',
            'RAJAONGKIR_DELIVERY_KEY' => 'API Key RajaOngkir (Delivery)',
        ];
    }
    public function messages() {
        return [
            'string' => ':attribute harus berupa teks.',
        ];
    }
}
