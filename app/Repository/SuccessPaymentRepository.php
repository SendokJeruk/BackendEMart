<?php

namespace App\Repository;

use App\Models\Income;
use App\Models\Transaction;
use App\Models\DetailIncome;
use Illuminate\Support\Facades\DB;

class SuccessPaymentRepository
{
    public static function PaymentSuccess($kodeTransaksi)
    {
        $transaction = Transaction::with('detail_transaction.product.user')
            ->where('kode_transaksi', $kodeTransaksi)
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        DB::beginTransaction();

        try {
            // Debug array untuk log hasil akhir
            $debugIncomes = [];

            // Kurangi stok produk
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

            foreach ($groupedByUser as $userId => $details) {
                $total = $details->sum('subtotal');

                $income = Income::create([
                    'user_id' => $userId,
                    'jumlah_total' => $total,
                    'status' => 'pending',
                ]);

                $detailIncomeList = [];

                foreach ($details as $detail) {
                    $createdDetail = $income->detail_incomes()->create([
                        'transaction_id' => $transaction->id,
                        'jumlah' => $detail->subtotal,
                    ]);

                    $detailIncomeList[] = $createdDetail;
                }

                $debugIncomes[] = [
                    'income' => $income,
                    'detail_incomes' => $detailIncomeList
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil diproses.',
                'debug' => $debugIncomes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses transaksi',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(), // Bisa dihapus kalau terlalu panjang
            ], 500);
        }
    }
}
