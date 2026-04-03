<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index()
    {
        // ngambil semua isi keranjang user yang lagi login beserta detail produk dan tokonya
        $Cart = Cart::where('user_id', auth()->id())->with('cart_detail.product.user.toko',)->get();
        return response()->json([
            'status' => 'Success',
            'message' => 'Success Get Cart',
            'data' => $Cart
        ]);

    }
}
