<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() {
        return [
            'nama_product' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'berat' => 'required|numeric|min:0',
            'foto_cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status_produk' => 'required|in:draft,publish',
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
            'required' => ':attribute wajib diisi.',
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
