<?php
namespace App\Http\Requests\Foto;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'foto' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
        public function attributes() {
        return [
            'foto' => 'foto',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diunggah.',
            'mimes' => 'format foto tidak valid.',
            'max' => 'ukuran file maksimal 2MB.',
        ];
    }
}
