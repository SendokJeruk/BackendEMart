<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\DetailShipment;
use App\Models\Transaction;
use App\Services\RajaOngkirService;


class ShipmentService
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function createShipment(string $kode_transaksi, array $dataOngkir)
    {
        $transaction = Transaction::where('kode_transaksi', $kode_transaksi)->firstOrFail();

        // Cek shipment yang sudah ada
        $existingShipment = Shipment::where('kode_transaksi', $kode_transaksi)->first();

        if ($existingShipment)
            return; // jika sudah ada, hentikan

        // Group detail transaksi berdasarkan user_id
        $groupedByUser = $transaction->detail_transaction->groupBy(fn($item) => $item->product->user_id);

        foreach ($groupedByUser as $userId => $details) {
            // Ambil toko_id user ini dari salah satu produk
            $tokoId = $details->first()->product->toko_id ?? null;
            if (!$tokoId)
                continue;

            // Cari data ongkir untuk toko ini
            $ongkirForToko = collect($dataOngkir)->firstWhere('toko_id', $tokoId);
            if (!$ongkirForToko)
                continue;

            $shipment = Shipment::create([
                'kode_transaksi' => $transaction->kode_transaksi,
                'kurir' => $ongkirForToko['kurir'], // pakai kurir sesuai toko
                'status_pengiriman' => 'dibuat',
            ]);

            foreach ($details as $detail) {
                DetailShipment::create([
                    'id_shipment' => $shipment->id,
                    'detail_transaksi_id' => $detail->id,
                ]);
            }
        }
    }


    public function trackShipment($id_shipment)
    {
        $shipment = Shipment::findOrFail($id_shipment);

        if (!$shipment->kode_resi || !$shipment->kurir) {
            return response()->json([
                'message' => 'Resi atau kurir tidak tersedia untuk pengiriman ini.',
            ], 400);
        }

        $trackingInfo = $this->rajaOngkir->trackShipment($shipment->kurir, $shipment->kode_resi);

        if (!$trackingInfo) {
            return response()->json([
                'message' => 'Gagal mendapatkan informasi pelacakan.',
            ], 500);
        }

        return response()->json([
            'message' => 'Berhasil mendapatkan informasi pelacakan.',
            'data' => $trackingInfo,
        ]);

    }
}
