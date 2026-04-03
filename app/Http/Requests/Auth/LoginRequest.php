<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class LoginRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }
        public function attributes() {
        return [
            'email' => 'email',
            'password' => 'password',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => 'format :attribute tidak valid.',
        ];
    }
}
