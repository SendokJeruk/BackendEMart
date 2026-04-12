<?php
namespace App\Http\Requests\DetailCart;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('Cart_detail')->cart->user_id === auth()->id();
    }
    public function rules() {
        return [
            'product_id' => 'required',
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
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.',
        ];
    }
}
