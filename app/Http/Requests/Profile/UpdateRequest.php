<?php
namespace App\Http\Requests\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'name'        => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'no_telp'     => 'required|numeric|digits_between:10,13',
            'password'    => [ 'nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols() ],
            'role_id'     => 'nullable|integer',
            'foto_profil' => 'nullable|file|image|max:2048',
        ];
    }
        public function attributes() {
        return [
            'name' => 'nama',
            'email' => 'email',
            'no_telp' => 'nomor telepon',
            'password' => 'password',
            'role_id' => 'role',
            'foto_profil' => 'foto profil',
        ];
    }
    public function messages() {
        return [
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'email' => 'format :attribute tidak valid.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'image' => ':attribute harus berupa gambar.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
        ];
    }
}
