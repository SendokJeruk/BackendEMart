<?php
namespace App\Http\Requests\RequestSeller;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'note'          => 'required|string|max:100',
            'nik'           => 'required|digits:16|unique:request_sellers,nik',
            'nama_lengkap'  => 'required|string|max:255',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat_ktp'    => 'required|string|max:500',
            'foto_ktp'      => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
        public function attributes() {
        return [
            'note' => 'catatan',
            'nik' => 'NIK',
            'nama_lengkap' => 'nama lengkap',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'alamat_ktp' => 'alamat KTP',
            'foto_ktp' => 'foto KTP',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'digits' => ':attribute harus terdiri dari :digits digit.',
            'unique' => ':attribute sudah digunakan.',
            'date' => 'format :attribute nggak bener.',
            'before' => 'tanggal harus sebelum hari ini.',
            'in' => 'jenis kelamin tidak valid.',
            'max' => ':attribute maksimal :max karakter.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => 'format gambar tidak valid.',
        ];
    }
}
