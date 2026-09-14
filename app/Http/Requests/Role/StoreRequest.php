<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'nama_role' => 'required|string|max:100',
        ];
    }
        public function attributes() {
        return [
            'nama_role' => 'nama role',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }
}
