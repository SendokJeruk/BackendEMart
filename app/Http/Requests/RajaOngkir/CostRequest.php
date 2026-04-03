<?php
namespace App\Http\Requests\RajaOngkir;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class CostRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'origin'      => 'required',
            'destination' => 'required',
            'weight'      => 'required|integer',
            'courier'     => 'required',
            'price'       => 'required'
        ];
    }
        public function attributes() {
        return [
            'origin' => 'asal',
            'destination' => 'tujuan',
            'weight' => 'berat',
            'courier' => 'kurir',
            'price' => 'harga',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka.',
        ];
    }
}
