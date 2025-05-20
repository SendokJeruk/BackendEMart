<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
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
                return $detail->product->berat * $detail->jumlah;
            });

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->status = "Proses";
            $transaction->tanggal_transaksi = now();
            $transaction->kode_transaksi = 'SJK-' . time() . strtoupper(Str::random(5));
            $transaction->total_harga = $user->cart->total_harga;
            $transaction->total_berat = $totalBerat;
            $transaction->save();

            foreach ($user->cart->cart_detail as $cartDetail) {
                DetailTransaction::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cartDetail->product_id,
                    'harga' => $cartDetail->harga / $cartDetail->jumlah,
                    'jumlah' => $cartDetail->jumlah,
                    'subtotal' => $cartDetail->harga,
                    'totalberat' => $cartDetail->product->berat * $cartDetail->jumlah,
                ]);


                $cartDetail->product->stock -= $cartDetail->jumlah;
                $cartDetail->product->save();
            }


            $user->cart->cart_detail()->delete();
            $user->cart->total_harga = 0;
            $user->cart->total_jumlah = 0;
            $user->cart->save();

            return response()->json([
                'message' => 'Checkout berhasil',
                'data' => $transaction->load('detail_transaction.product')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
