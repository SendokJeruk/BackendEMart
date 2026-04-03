<?php
namespace App\Http\Requests\DetailTransaction;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'transaction_id' => 'required',
            'product_id' => 'required',
            'jumlah' => 'required|integer|min:1',
        ];
    }
        public function attributes() {
        return [
            'transaction_id' => 'transaksi',
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
