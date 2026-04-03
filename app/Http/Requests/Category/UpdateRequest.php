<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return [ 'nama_category' => 'required|max:100', ]; }
        public function attributes() {
        return [
            'nama_category' => 'nama kategori',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'max' => 'maksimal :max karakter.' ]; }
}
