<?php
namespace App\Http\Requests\Transaction;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class CreateTransactionRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'payment_type' => 'nullable',
            'data_ongkir' => 'required',
        ];
    }
        public function attributes() {
        return [
            'payment_type' => 'tipe pembayaran',
            'data_ongkir' => 'data ongkir',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
        ];
    }
}
