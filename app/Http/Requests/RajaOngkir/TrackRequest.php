<?php
namespace App\Http\Requests\RajaOngkir;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class TrackRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'waybill' => 'required|string',
            'courier' => 'required|string',
        ];
    }
        public function attributes() {
        return [
            'waybill' => 'resi',
            'courier' => 'kurir',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
        ];
    }
}
