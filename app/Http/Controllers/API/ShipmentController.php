<?php

namespace App\Http\Controllers\API;

use App\Models\Income;
use App\Models\Shipment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShipmentController extends Controller
{
    public function getAllPengiriman()
    {
        // return Shipment::with(['transaction.user'])->paginate(10);
        $pengiriman = Shipment::with(['transaction.user'])
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->paginate(10);

        return response()->json([
            'message' => 'Berhasil mendapatkan data pengiriman',
            'data' => $pengiriman
        ]);
    }

    public function getPengirimanByKodeTransaksi($kode_transaksi)
    {
        $shipments = Shipment::with([
            'transaction.user',
            'detail_shipments.detail_transaction.product.user.toko'
        ])
            ->where('kode_transaksi', $kode_transaksi)
            ->whereHas('transaction', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        if ($shipments->isEmpty()) {
            return response()->json([
                'message' => 'Data pengiriman tidak ditemukan',
            ], 404);
        }

        $result = [];

        foreach ($shipments as $shipment) {
            foreach ($shipment->detail_shipments as $detailShipment) {
                $detailTransaksi = $detailShipment->detail_transaction;
                $product = $detailTransaksi->product;
                $toko = $product->user->toko;

                // pakai ID toko sebagai group key
                $group = $toko->id;

                if (!isset($result[$group])) {
                    $result[$group] = [
                        'toko_id' => $toko->id,
                        'nama_toko' => $toko->nama_toko ?? null,
                        'kode_domestik' => $toko->alamatToko->kode_domestik ?? null,
                        'items' => []
                    ];
                }

                $result[$group]['items'][] = [
                    'shipment_id' => $shipment->id,
                    'kode_transaksi' => $shipment->kode_transaksi,
                    'status_pengiriman' => $shipment->status_pengiriman,
                    'product_id' => $product->id,
                    'harga' => $detailTransaksi->harga,
                    'jumlah' => $detailTransaksi->jumlah,
                    'subtotal' => $detailTransaksi->subtotal,
                    'totalberat' => $detailTransaksi->totalberat,
                ];
            }
        }



        return response()->json([
            'message' => 'Berhasil mendapatkan data pengiriman dengan kode transaksi ' . $kode_transaksi,
            'data' => $result
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

        $pengiriman = Shipment::create([
            'kode_transaksi' => $request->kode_transaksi,
            'status_pengiriman' => $request->status_pengiriman,
            'kode_resi' => $request->kode_resi,
            'kurir' => $request->kurir,
            'plat_nomor' => $request->plat_nomor,
            'estimasi_tiba' => $request->estimasi_tiba,
            'bukti_pengiriman' => $request->bukti_pengiriman,
        ]);

        return response()->json([
            'message' => 'Berhasil Menambahkan Data Pengiriman',
            'data' => $pengiriman
        ], 201);
    }

    public function update(Request $request, Shipment $shipment)
    {
        $request->validate([
            'kode_transaksi' => 'required' . $shipment->id,
            'status_pengiriman' => 'required|string|in:dibuat,dijadwalkan,kurir_ditugaskan,dalam_proses,tiba',
            'resi' => 'nullable|string',
            'ekspedisi' => 'nullable|string',
            'plat_nomor' => 'nullable|string',
            'estimasi_tiba' => 'nullable|date',
            'bukti_pengiriman' => 'nullable|string',
        ]);

        $shipment->update([
            'kode_transaksi' => $request->kode_transaksi,
            'status_pengiriman' => $request->status_pengiriman,
            'kode_resi' => $request->resi,
            'kurir' => $request->ekspedisi,
            'plat_nomor' => $request->plat_nomor,
            'estimasi_tiba' => $request->estimasi_tiba,
            'bukti_pengiriman' => $request->bukti_pengiriman,
        ]);

        return response()->json([
            'message' => 'Berhasil Mengupdate Data Pengiriman',
            'data' => $shipment
        ]);
    }

    public function delete(Shipment $shipment)
    {
        $shipment->delete();
        return response()->json([
            'message' => 'Berhasil Menghapus Data Pengiriman',
        ]);
    }

    public function confirmReceived(Shipment $pengiriman)
    {
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

        // Ambil detail transaksi hanya dari shipment ini
        $details = $pengiriman->detail_shipments()->with('detail_transaction.product.user')->get();

        // Group per user toko
        $groupedByUser = $details->groupBy(fn($detailShipment) => $detailShipment->detail_transaction->product->user_id);

        foreach ($groupedByUser as $userId => $detailShipments) {
            $total = $detailShipments->sum(fn($ds) => $ds->detail_transaction->subtotal);

            $income = Income::firstOrNew(['user_id' => $userId]);
            $income->jumlah_total = ($income->exists ? $income->jumlah_total : 0) + $total;
            $income->total_penjualan = ($income->exists ? $income->total_penjualan : 0) + 1;
            $income->save();

            foreach ($detailShipments as $ds) {
                $income->detail_incomes()->create([
                    'detail_transaction_id' => $ds->detail_transaction->id,
                    'jumlah' => $ds->detail_transaction->subtotal,
                ]);
            }
        }

        return response()->json([
            'message' => 'Pengiriman telah dikonfirmasi sebagai diterima.',
            'data' => $pengiriman
        ]);
    }

}
