<?php

namespace App\Repository;

use App\Models\Income;
use App\Models\Pengiriman;
use App\Models\Transaction;
use App\Models\DetailIncome;
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
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stok tidak cukup untuk produk: ' . $product->nama_product
                    ], 400);
                }
            }

            // Kelompokkan detail transaksi berdasarkan user_id (penjual)
            $groupedByUser = $transaction->detail_transaction->groupBy(fn($item) => $item->product->user_id);
            Log::info('Success get groupedByUser => ' . json_encode($groupedByUser));
            foreach ($groupedByUser as $userId => $details) {
                $total = $details->sum('subtotal');

                $income = Income::firstOrNew(['user_id' => $userId]);
                $income->jumlah_total = ($income->exists ? $income->jumlah_total : 0) + $total;
                $income->total_penjualan += 1;
                $income->save();

                $detailIncomeList = [];

                foreach ($details as $detail) {
                    $createdDetail = $income->detail_incomes()->create([
                        'detail_transaction_id' => $detail->id,
                        'jumlah' => $detail->subtotal,
                    ]);

                    $detailIncomeList[] = $createdDetail;
                }

                $debugIncomes[] = [
                    'income' => $income,
                    'detail_incomes' => $detailIncomeList
                ];
            }

            Pengiriman::create([
                'kode_transaksi' => $transaction->kode_transaksi,
                'status_pengiriman' => 'dibuat',
            ]);

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
