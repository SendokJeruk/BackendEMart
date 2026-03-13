<?php

namespace App\Http\Controllers\API;

use App\Models\Income;
use App\Models\Pengiriman;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PengirimanController extends Controller
{

    public function getAllPengiriman()
    {
        // return Pengiriman::with(['transaction.user'])->paginate(10);
        $pengiriman = Pengiriman::with(['transaction.user'])
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Shipping data retrieved successfully',
            'data' => $pengiriman
        ]);
    }

    public function getPengirimanByKodeTransaksi($kode_transaksi)
    {
        $pengiriman = Pengiriman::with(['transaction.user'])
            ->where('kode_transaksi', $kode_transaksi)
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();

        if (!$pengiriman) {
            return response()->json([
                'message' => 'Data pengiriman tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Shipping data retrieved successfully',
            'data' => $pengiriman
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_transaksi' => 'required|unique:pengirimen,kode_transaksi',
            'status_pengiriman' => 'required|string|in:dibuat,dijadwalkan,kurir_ditugaskan,dalam_proses,tiba',
            'kode_resi' => 'nullable|string',
            'kurir' => 'nullable|string',
            'plat_nomor' => 'nullable|string',
            'estimasi_tiba' => 'nullable|date',
            'bukti_pengiriman' => 'nullable|string',
        ]);

        $pengiriman = Pengiriman::create([
            'kode_transaksi' => $request->kode_transaksi,
            'status_pengiriman' => $request->status_pengiriman,
            'kode_resi' => $request->kode_resi,
            'kurir' => $request->kurir,
            'plat_nomor' => $request->plat_nomor,
            'estimasi_tiba' => $request->estimasi_tiba,
            'bukti_pengiriman' => $request->bukti_pengiriman,
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Shipping data added successfully',
            'data' => $pengiriman
        ], 201);
    }

    public function update(Request $request, Pengiriman $pengiriman)
    {
        $request->validate([
            'kode_transaksi' => 'required|unique:pengirimen,kode_transaksi,' . $pengiriman->id,
            'status_pengiriman' => 'required|string|in:dibuat,dijadwalkan,kurir_ditugaskan,dalam_proses,tiba',
            'resi' => 'nullable|string',
            'ekspedisi' => 'nullable|string',
            'plat_nomor' => 'nullable|string',
            'estimasi_tiba' => 'nullable|date',
            'bukti_pengiriman' => 'nullable|string',
        ]);

        $pengiriman->update([
            'kode_transaksi' => $request->kode_transaksi,
            'status_pengiriman' => $request->status_pengiriman,
            'kode_resi' => $request->resi,
            'kurir' => $request->ekspedisi,
            'plat_nomor' => $request->plat_nomor,
            'estimasi_tiba' => $request->estimasi_tiba,
            'bukti_pengiriman' => $request->bukti_pengiriman,
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Shipping data updated successfully',
            'data' => $pengiriman
        ]);
    }

    public function delete(Pengiriman $pengiriman)
    {
        $pengiriman->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Shipping data updated successfully',
        ]);
    }

    public function confirmReceived($kode_transaksi) {
        $pengiriman = Pengiriman::where('kode_transaksi', $kode_transaksi)
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();

        if (!$pengiriman) {
            return response()->json([
                'message' => 'Data pengiriman tidak ditemukan',
            ], 404);
        }

        if ($pengiriman->status_pengiriman !== 'tiba') {
            return response()->json([
                'message' => 'Pengiriman belum tiba. Tidak dapat mengonfirmasi penerimaan.',
            ], 400);
        }

        $pengiriman->status_pengiriman = 'diterima';
        $pengiriman->save();

        $transaction = Transaction::with('detail_transaction.product.user')
                ->where('kode_transaksi', $kode_transaksi)
                ->first();

        $groupedByUser = $transaction->detail_transaction->groupBy(fn($item) => $item->product->user_id);
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

        return response()->json([
            'message' => 'Pengiriman telah dikonfirmasi sebagai diterima.',
            'data' => $pengiriman
        ]);
    }

}
