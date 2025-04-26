<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class MidtransCallback extends Controller
{
    public function callback(Request $request)
    {
        // $method = $request->input('_method') ?? $request->method();

        // if (strtoupper($method) !== 'PUT' && strtoupper($method) !== 'POST') {
        //     return response()->json(['message' => 'Invalid method'], 405);
        // }
        // Log semua data yang masuk untuk debug
        Log::info('Received callback: ', $request->all());

        // Cek jika semua parameter yang dibutuhkan ada
        $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
        foreach ($requiredFields as $field) {
            if (!$request->has($field)) {
                Log::error('Missing required field: ' . $field);
                return response('Missing required field: ' . $field, 400);
            }
        }

        // Ambil server key dari konfigurasi
        $serverKey = config('midtrans.server_key');

        // Cek signature
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // Log untuk debug signature key
        Log::info('Calculated Hash: ' . $hashedKey);
        Log::info('Received Signature Key: ' . $request->signature_key);

        // Jika signature tidak valid
        if ($hashedKey !== $request->signature_key) {
            Log::error('Invalid signature key for order ID: ' . $request->order_id);
            return response('Invalid signature key', 403);
        }

        // Ambil data transaksi
        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;

        // Cek jika order ditemukan
        $order = Transaction::where('kode_transaksi', $orderId)->first();

        if (!$order) {
            Log::error('Order not found: ' . $orderId);
            return response('Order not found', 404);
        }

        // Log status transaksi
        Log::info('Transaction Status: ' . $transactionStatus);

        // Handle status transaksi
        switch ($transactionStatus) {
            case 'capture':
                if ($request->payment_type == 'credit_card') {
                    if ($request->fraud_status == 'challenge') {
                        Log::info('Fraud challenge for order ID: ' . $orderId);
                        $order->update(['status' => 'pending']);
                    } else {
                        $order->update(['status' => 'success']);
                    }
                }
                break;
            case 'settlement':
                $order->update(['status' => 'success']);
                break;
            case 'pending':
                $order->update(['status' => 'pending']);
                break;
            case 'deny':
                $order->update(['status' => 'failed']);
                break;
            case 'expire':
                $order->update(['status' => 'expired']);
                break;
            case 'cancel':
                $order->update(['status' => 'canceled']);
                break;
            default:
                $order->update(['status' => 'unknown']);
                break;
        }

        // Log perubahan status order
        Log::info('Order updated for order ID: ' . $orderId . ' with status: ' . $order->status);

        // Kirim response OK ke Midtrans
        return response('OK', 200);
    }
}
