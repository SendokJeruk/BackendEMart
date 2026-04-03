<?php
namespace App\Http\Requests\CategoryProduct;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() {
        $product = \App\Models\Product::find($this->product_id);
        return $product && (int)$product->user_id === (int)auth()->id();
    }
    public function rules() {
        return [ 'category_id' => 'required|exists:categories,id', 'product_id' => 'required|exists:products,id' ];
    }
        public function attributes() {
        return [
            'category_id' => 'kategori',
            'product_id' => 'produk',
        ];
    }
    public function messages() { return [ 'required' => ':attribute wajib diisi.', 'exists' => ':attribute tidak ditemukan dalam data.' ]; }
}
