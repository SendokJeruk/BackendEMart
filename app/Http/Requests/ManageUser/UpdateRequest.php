<?php
namespace App\Http\Requests\ManageUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'name'     => 'nullable|string|min:3|max:50',
            'email'    => 'nullable|email|unique:users,email',
            'no_telp'  => 'nullable|numeric|digits_between:10,13',
            'password' => ['nullable', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
            'role_id'  => 'nullable',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'email' => 'format :attribute tidak valid.',
            'unique' => ':attribute sudah digunakan.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => 'format gambar tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
        ];
    }
}
