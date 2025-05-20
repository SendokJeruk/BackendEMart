<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    public function checkout()
    {
        try {
            $user = auth()->user();

            if (!$user->cart || $user->cart->cart_detail->isEmpty()) {
                return response()->json([
                    'message' => 'Keranjang tidak boleh kosong, masukan produk ke keranjang terlebih dahulu'
                ], 422);
            }

            $totalBerat = $user->cart->cart_detail->sum(function ($detail) {
                return $detail->product->berat;
            });

            $transaction = new Transaction();
            $transaction->user_id = auth()->id();
            $transaction->status = "Proses";
            $transaction->tanggal_transaksi = now();
            $transaction->kode_transaksi = 'SJK-' . time() . strtoupper(Str::random(5));
            $transaction->total_harga = $user->cart->total_harga;
            $transaction->total_berat = $totalBerat;
            $transaction->save();

            // $transaction->id; ambil id

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
