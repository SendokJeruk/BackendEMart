<?php
namespace App\Http\Requests\Transaction;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('transaction')->user_id === auth()->id();
    }
    public function rules() {
        return [
            'status' => 'nullable|string',
            'total_ongkir' => 'nullable|numeric'
        ];
    }
        public function attributes() {
        return [
            'status' => 'status',
            'total_ongkir' => 'total ongkir',
        ];
    }
    public function messages() {
        return [
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
        ];
    }
}
