<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class VerifyRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return [ 'token' => 'required|string', ]; }
        public function attributes() {
        return [
            'token' => 'token',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'string' => ':attribute harus berupa teks.' ]; }
}
