<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
/**
 * @mixin \Illuminate\Http\Request
 */
class ResetPasswordRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => [ 'required', 'confirmed', Password::min(8)->mixedCase()->letters()->numbers()->symbols() ],
        ];
    }
        public function attributes() {
        return [
            'token' => 'token',
            'email' => 'email',
            'password' => 'password',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'string' => ':attribute harus berupa teks.', 'email' => 'format email tidak valid.', 'confirmed' => 'konfirmasi password tidak cocok.' ]; }
}
