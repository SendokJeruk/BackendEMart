<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class SendResetPasswordRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return [ 'email' => 'required|email|exists:users,email' ]; }
        public function attributes() {
        return [
            'email' => 'email',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'email' => 'format email tidak valid.', 'exists' => 'email tidak ditemukan.' ]; }
}
