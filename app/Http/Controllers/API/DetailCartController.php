<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Cart_detail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\DetailCart\StoreRequest;
use App\Http\Requests\DetailCart\UpdateRequest;

class DetailCartController extends Controller
{
    public function store(StoreRequest $request)
    {
        // ngecek stok produk, kalo aman ditambahin ke keranjang, trus update total harga keranjang
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Produk Not Found.'], 404);
        }

        if ($request->jumlah > $product->stock) {
            return response()->json(['message' => 'Not enough stock available.'], 422);
        }

        $userId = auth()->id();
        $cart = Cart::firstOrCreate(['user_id' => $userId], ['total_harga' => 0, 'total_jumlah' => 0]);

        $cartDetail = Cart_detail::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartDetail) {
            $cartDetail->jumlah += $request->jumlah;
            $cartDetail->harga = $cartDetail->jumlah * $product->harga;
            $cartDetail->save();
        } else {
            $cartDetail = new Cart_detail();
            $cartDetail->cart_id = $cart->id;
            $cartDetail->product_id = $request->product_id;
            $cartDetail->jumlah = $request->jumlah;
            $cartDetail->harga = $product->harga * $request->jumlah;
            $cartDetail->save();
        }

        $cart->total_harga = $cart->cart_detail->sum('harga');
        $cart->total_jumlah = $cart->cart_detail->sum('jumlah');
        $cart->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Item added to cart successfully.',
            'data' => $cartDetail
        ], 201);
    }

    public function update(UpdateRequest $request, Cart_detail $Cart_detail)
    {
        // ngubah jumlah barang di keranjang, cek stok lagi, dan itung ulang total harga
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($request->jumlah > $product->stock) {
            return response()->json(['message' => 'Not enough product stock'], 422);
        }

        $oldAmount = $Cart_detail->harga;
        $oldJumlah = $Cart_detail->jumlah;
        $newAmount = $product->harga * $request->jumlah;

        $Cart_detail->product_id = $request->product_id;
        $Cart_detail->jumlah = $request->jumlah;
        $Cart_detail->harga = $newAmount;
        $Cart_detail->save();

        $cart = $Cart_detail->cart;
        if ($cart != null) {
            $cart->total_harga = $cart->total_harga - $oldAmount + $newAmount;
            $cart->total_jumlah = $cart->total_jumlah - $oldJumlah + $request->jumlah;
            $cart->save();
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Cart details updated successfully',
            'data' => $Cart_detail
        ]);
    }

    public function delete(Cart_detail $Cart_detail)
    {
        // ngapus satu barang dari keranjang, sekalian ngurangin total harga dan jumlah di keranjang
        if ($Cart_detail->cart->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $cart = $Cart_detail->cart;
        if ($cart) {
            $cart->total_harga -= $Cart_detail->harga;
            $cart->total_jumlah -= $Cart_detail->jumlah;
            $cart->total_harga = max(0, $cart->total_harga);
            $cart->total_jumlah = max(0, $cart->total_jumlah);
            $cart->save();
        }
        $Cart_detail->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Item removed from cart successfully'
        ]);
    }
}
