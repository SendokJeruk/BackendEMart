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
        try {
            $Cart = Cart::where('user_id', auth()->id())->with('cart_detail')->get();
            return response()->json([
                'message' => 'Success Get Cart',
                'data' => $Cart
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
