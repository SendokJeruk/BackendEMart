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
        $Cart = Cart::where('user_id', auth()->id())->with('cart_detail.product',)->get();
        return response()->json([
            'message' => 'Success Get Cart',
            'data' => $Cart
        ]);

    }
}
