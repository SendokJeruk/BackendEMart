<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;

class CheckoutController extends Controller
{
    public function checkoutAll()
    {

        $user = auth()->user();

        if (!$user->cart || $user->cart->cart_detail->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty, please add products first'
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
        }

        $user->cart->cart_detail()->delete();
        $user->cart->total_harga = 0;
        $user->cart->total_jumlah = 0;
        $user->cart->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Checkout Successful',
            'data' => $transaction->load('detail_transaction.product')
        ]);

    }

    public function checkout(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'cart_detail_ids' => 'required|array|min:1',
            'cart_detail_ids.*' => 'integer|exists:cart_details,id',
        ]);

        $cartDetails = $user->cart->cart_detail()
            ->whereIn('id', $request->cart_detail_ids)
            ->get();

        if ($cartDetails->count() !== count($request->cart_detail_ids)) {
            throw new AuthorizationException();
        }

        if ($cartDetails->isEmpty()) {
            return response()->json([
                'message' => 'Item cart tidak ditemukan atau bukan milik Anda.'
            ], 422);
        }

        $totalBerat = $cartDetails->sum(fn($detail) => $detail->product->berat * $detail->jumlah);
        $totalHarga = $cartDetails->sum('harga');

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->status = "Proses";
        $transaction->tanggal_transaksi = now();
        $transaction->kode_transaksi = 'SJK-' . time() . strtoupper(Str::random(5));
        $transaction->total_harga = $totalHarga;
        $transaction->total_berat = $totalBerat;
        $transaction->save();

        foreach ($cartDetails as $cartDetail) {
            DetailTransaction::create([
                'transaction_id' => $transaction->id,
                'product_id' => $cartDetail->product_id,
                'harga' => $cartDetail->harga / $cartDetail->jumlah,
                'jumlah' => $cartDetail->jumlah,
                'subtotal' => $cartDetail->harga,
                'totalberat' => $cartDetail->product->berat * $cartDetail->jumlah,
            ]);
        }

        $user->cart->cart_detail()->whereIn('id', $request->cart_detail_ids)->delete();

        $user->cart->total_harga = $user->cart->cart_detail()->sum('harga');
        $user->cart->total_jumlah = $user->cart->cart_detail()->sum('jumlah');
        $user->cart->save();

        return response()->json([
            'message' => 'Checkout berhasil',
            'data' => $transaction->load('detail_transaction.product')
        ]);
    }

}
