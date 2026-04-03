<?php
namespace App\Http\Requests\FotoProduct;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class StoreRequest extends FormRequest {
    public function authorize() {
        $product = \App\Models\Product::findOrFail($this->product_id);
        return $product->user_id === auth()->id();
    }
    public function rules() {
        return [
            'foto_id'    => 'required',
            'product_id' => 'required',
        ];
    }
        public function attributes() {
        return [
            'foto_id' => 'foto',
            'product_id' => 'produk',
        ];
    }
    public function messages() {
        return [
            'required' => ':attribute wajib diisi.',
        ];
    }
}
