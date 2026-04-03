<?php
namespace App\Http\Requests\Transaction;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'status' => 'required',
        ];
    }
        public function attributes() {
        return [
            'status' => 'status',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
        ];
    }
}
