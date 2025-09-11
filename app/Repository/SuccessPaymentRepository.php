<?php

namespace App\Repository;

use App\Models\Income;
use App\Models\Pengiriman;
use App\Models\Transaction;
use App\Models\DetailIncome;
use App\Models\DetailShipment;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuccessPaymentRepository
{
    public static function PaymentSuccess($kodeTransaksi)
    {
        try {
            $transaction = Transaction::with('detail_transaction.product.user')
                ->where('kode_transaksi', $kodeTransaksi)
                ->first();

            if (!$transaction) {
                return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
            }

            DB::beginTransaction();


            $debugIncomes = [];

            foreach ($transaction->detail_transaction as $detail) {
                $product = $detail->product;
                if ($product->stock >= $detail->jumlah) {
                    $product->decrement('stock', $detail->jumlah);
                    $product->increment('terjual', $detail->jumlah);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stok tidak cukup untuk produk: ' . $product->nama_product
                    ], 400);
                }
            }

            $groupedByUser = $transaction->detail_transaction->groupBy(fn($item) => $item->product->user_id);

            foreach ($groupedByUser as $userId => $details) {
                $shipment = Shipment::create([
                    'kode_transaksi' => $transaction->kode_transaksi,
                    'status_pengiriman' => 'dibuat',
                ]);

                foreach ($details as $detail) {
                    DetailShipment::create([
                        'id_shipment' => $shipment->id,
                        'detail_transaksi_id' => $detail->id,
                    ]);
                }
            }

            DB::commit();

            return response('OK', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses transaksi',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
