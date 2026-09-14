<?php
namespace App\Http\Requests\DetailTransaction;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('detailTransaction')->transaction->user_id === auth()->id();
    }
    public function rules() {
        return [
            'transaction_id' => 'nullable',
            'product_id' => 'nullable',
            'jumlah' => 'nullable|integer|min:1',
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
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.',
        ];
    }
}
