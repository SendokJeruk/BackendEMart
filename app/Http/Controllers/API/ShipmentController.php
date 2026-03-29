<?php

namespace App\Http\Controllers\API;

use App\Models\Income;
use App\Models\Shipment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SellerBalance;
use App\Services\ShipmentService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class ShipmentController extends Controller
{
    protected $shipment;

    public function __construct(ShipmentService $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getAllPengirimanSeller()
    {
        $pengiriman = Shipment::with([
            'transaction.user',
            'detail_shipments.detail_transaction.product',
            'detail_shipments.detail_transaction.rating:id,detail_transaction_id,rating'
        ])
        ->whereHas('detail_shipments.detail_transaction.product', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->paginate(10);

        return response()->json([
            'message' => 'Berhasil mendapatkan data pengiriman',
            'data' => $pengiriman
        ]);
    }

    public function getAllPengirimanBuyer()
    {
        // return Shipment::with(['transaction.user'])->paginate(10);
        $user = Auth::user();

        $pengiriman = Shipment::with([
            'transaction.user',
            'detail_shipments.detail_transaction.product',
            'detail_shipments.detail_transaction.rating:id,detail_transaction_id,rating'
        ])
        // ->whereHas('detail_shipments.detail_transaction.product', function ($query) {
        //     $query->where('user_id', Auth::id());
        // })
        ->whereHas('transaction', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->paginate(10);

        Log::info($pengiriman);

        return response()->json([
            'message' => 'Berhasil mendapatkan data pengiriman',
            'data' => $pengiriman
        ]);
    }

    public function getPengirimanById($id)
    {
        $pengiriman = Shipment::with(['detail_shipments.detail_transaction.product.user.toko', 'transaction'])
            ->where('id', $id)
            ->firstOrFail();

        if ($pengiriman->transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        if ($pengiriman->kode_resi && $pengiriman->kurir) {
            $shippingData = $this->shipment->trackShipment($pengiriman->id);
            if ($shippingData) {
                $pengiriman->shippingData = $shippingData;
            }
        }

        return response()->json([
            'message' => 'Berhasil mendapatkan data pengiriman dengan ID ' . $id,
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
                    'nama_product' => $product->nama_product,
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
        Log::info("INI CEK DATA YANG MASUK");
        Log::info($request);
        $request->validate([
            'kode_transaksi' => 'required|unique:shipments,kode_transaksi',
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
            'kode_transaksi' => 'required',
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
        $isOwner = $shipment->detail_shipments()
            ->whereHas('detail_transaction.product', function ($query) {
                $query->where('user_id', auth()->id());
            })->exists();

        if (!$isOwner) {
            throw new AuthorizationException();
        }

        $shipment->delete();
        return response()->json([
            'message' => 'Berhasil Menghapus Data Pengiriman',
        ]);
    }

    public function confirmReceived(Shipment $pengiriman)
    {
        if ($pengiriman->transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        if ($pengiriman->status_pengiriman !== 'tiba') {
            return response()->json([
                'message' => 'Pengiriman belum tiba. Tidak dapat mengonfirmasi penerimaan.',
            ], 400);
        }

        $data = $this->shipment->confirmReceived($pengiriman);

        return response()->json([
            'message' => 'Pengiriman telah dikonfirmasi sebagai diterima.',
            'data' => $data
        ]);
    }

}
