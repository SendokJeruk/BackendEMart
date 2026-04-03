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
use App\Http\Requests\Shipment\StoreRequest;
use App\Http\Requests\Shipment\UpdateRequest;

class ShipmentController extends Controller
{
    protected $shipment;

    public function __construct(ShipmentService $shipment)
    {
        // ngejalanin fungsi __construct
        $this->shipment = $shipment;
    }

    public function getAllPengirimanSeller()
    {
        // ngambil pengiriman yang isinya produk punya seller yang login
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
        // ngambil daftar pengiriman dari transaksi yang dibikin user login
        $user = Auth::user();
        $pengiriman = Shipment::with([
            'transaction.user',
            'detail_shipments.detail_transaction.product',
            'detail_shipments.detail_transaction.rating:id,detail_transaction_id,rating'
        ])
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
        // ngambil detail pengiriman spesifik sekalian tracking via API kalo ada resinya
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
        // nyari pengiriman pake kode transaksi trus dikelompokkin per toko
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

    public function store(StoreRequest $request)
    {
        // bikin data pengiriman baru, set status n masukin resi/kurirnya
        Log::info("INI CEK DATA YANG MASUK");
        Log::info($request);
        
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

    public function update(UpdateRequest $request, Shipment $shipment)
    {
        // ngupdate rincian pengiriman kayak ganti status, resi, atau estimasi tiba
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
        // pastiin seller yang bersangkutan baru boleh ngapus data pengiriman
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
        // verifikasi pengiriman nyampe, trus mindahin uang ke saldo income seller
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
