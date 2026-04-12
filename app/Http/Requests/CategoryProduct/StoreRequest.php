<?php
namespace App\Http\Requests\CategoryProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    // public function authorize() {
    //     $product = \App\Models\Product::find($this->product_id);
    //     return $product && (int)$product->user_id === (int)auth()->id();
    // }
    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'category_id' => [
                'required',
                'exists:categories,id',
                Rule::unique('category_products', 'category_id')
                    ->where(function ($query) {
                        return $query->where('product_id', $this->product_id);
                    }),
            ],
        ];
    }
        public function attributes() {
        return [
            'category_id' => 'kategori',
            'product_id' => 'produk',
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan dalam data.',
            'category_id.unique' => ':attribute ini sudah terdaftar.',
        ];
    }
}
