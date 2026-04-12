<?php
namespace App\Http\Requests\Content;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'section' => 'required|in:login,dashboard',
        ];
    }
        public function attributes() {
        return [
            'image' => 'gambar',
            'section' => 'bagian',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'image' => ':attribute harus file gambar.', 'mimes' => 'format gambar tidak valid.', 'max' => 'ukuran file maksimal 2MB.', 'in' => 'section tidak valid.' ]; }
}
