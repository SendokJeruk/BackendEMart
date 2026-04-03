<?php
namespace App\Http\Requests\Checkout;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @mixin \Illuminate\Http\Request
 */
class CheckoutRequest extends FormRequest {
    public function authorize() {
        $user = auth()->user();
        if(!$user || !$user->cart) return false;
        $cartDetails = $user->cart->cart_detail()->whereIn('id', $this->cart_detail_ids ?? [])->get();
        return $cartDetails->count() === count($this->cart_detail_ids ?? []);
    }
    public function rules() {
        return [
            'cart_detail_ids' => 'required|array|min:1',
            'cart_detail_ids.*' => 'integer|exists:cart_details,id',
        ];
    }
        public function attributes() {
        return [
            'cart_detail_ids' => 'item keranjang',
            'cart_detail_ids.*' => 'item keranjang',
        ];
    }
    public function messages() { return [ 'required' => 'Harap pilih item untuk checkout.', 'array' => 'Format harus array.', 'exists' => 'Data cart tidak ditemukan.' ]; }
}
