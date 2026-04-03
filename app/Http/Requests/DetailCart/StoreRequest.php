<?php
namespace App\Http\Requests\DetailCart;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
        ];
    }
        public function attributes() {
        return [
            'product_id' => 'produk',
            'jumlah' => 'jumlah',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => 'produk tidak ditemukan.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.',
        ];
    }
}
