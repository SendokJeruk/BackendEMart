<?php
namespace App\Http\Requests\Rating;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'product_id' => 'required',
            'rating' => 'required|integer',
            'detail_transaction_id' => 'required',
            'deskripsi' => 'nullable'
        ];
    }
        public function attributes() {
        return [
            'product_id' => 'produk',
            'rating' => 'rating',
            'detail_transaction_id' => 'detail transaksi',
            'deskripsi' => 'deskripsi',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka.',
        ];
    }
}
