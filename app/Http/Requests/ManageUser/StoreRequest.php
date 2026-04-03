<?php
namespace App\Http\Requests\ManageUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'name'      => 'required|string|min:3|max:50',
            'email'     => 'required|email|unique:users,email',
            'no_telp'   => 'required|numeric|digits_between:10,13',
            'password'  => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
            'role_id'   => 'required',
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
            'required' => ':attribute wajib diisi.',
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
