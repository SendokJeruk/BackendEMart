<?php
namespace App\Http\Requests\Toko;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('toko')->user_id === auth()->id();
    }
    public function rules() {
        return [
            'nama_toko' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:255',
            'no_telp' => 'required|numeric|digits_between:10,13',
        ];
    }
        public function attributes() {
        return [
            'nama_toko' => 'nama toko',
            'deskripsi' => 'deskripsi',
            'no_telp' => 'nomor telepon',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
        ];
    }
}
