<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
/**
 * @mixin \Illuminate\Http\Request
 */
class RegisterRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'no_telp' => 'required|numeric|digits_between:10,13',
            'password' => [
                'required',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols()
            ],
            'role_id' => 'nullable',
        ];
    }
        public function attributes() {
        return [
            'name' => 'nama',
            'email' => 'email',
            'no_telp' => 'nomor telepon',
            'password' => 'password',
            'role_id' => 'role',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => 'format :attribute tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
        ];
    }
}
