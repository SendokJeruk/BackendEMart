<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class UpdateRequest extends FormRequest {
    public function authorize() {
        return $this->route('product')->user_id === auth()->id();
    }
    public function rules() {
        return [
            'nama_product' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'berat' => 'nullable|numeric|min:0',
            'foto_cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status_produk' => 'nullable|in:draft,publish',
        ];
    }
        public function attributes() {
        return [
            'nama_product' => 'nama produk',
            'deskripsi' => 'deskripsi',
            'harga' => 'harga',
            'stock' => 'stok',
            'berat' => 'berat',
            'foto_cover' => 'foto cover',
            'status_produk' => 'status produk',
        ];
    }
    public function messages() {
        return [
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => 'format foto tidak valid.',
            'in' => 'status produk tidak valid.',
        ];
    }
}
