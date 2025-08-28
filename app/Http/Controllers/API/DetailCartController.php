<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Cart_detail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DetailCartController extends Controller
{
    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        if ($request->jumlah > $product->stock) {
            return response()->json([
                'message' => 'Stok produk tidak cukup.',
            ], 422);
        }

        $amount = $product->harga * $request->jumlah;

        $cartDetail = new Cart_detail();
        $cartDetail->product_id = $request->product_id;
        $cartDetail->jumlah = $request->jumlah;
        $cartDetail->harga = $amount;

        $userId = auth()->id();
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['total_harga' => 0, 'total_jumlah' => 0]
        );

        $cart->total_harga += $amount;
        $cart->total_jumlah += $request->jumlah;
        $cart->save();

        $cartDetail->cart_id = $cart->id;
        $cartDetail->save();

        return response()->json([
            'message' => 'Item berhasil ditambahkan ke keranjang.',
            'data' => $cartDetail
        ]);
    }

    public function update(Request $request, Cart_detail $Cart_detail)
    {

        $validate = Validator::make($request->all(), [
            'product_id' => 'required',
            'jumlah' => 'required|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak di temukan'
            ], 404);
        }

        if ($request->jumlah > $product->stock) {
            return response()->json([
                'message' => 'Stock Produk Tidak Cukup',
            ], 422);
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
            'message' => 'berhasil perbarui cart detail',
            'data' => $Cart_detail
        ]);

    }


    public function delete(Cart_detail $Cart_detail)
    {

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
            'message' => 'Item berhasil dihapus dari keranjang.'
        ]);
    }
}
