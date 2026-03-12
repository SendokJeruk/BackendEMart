<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Transaction;
use App\Models\DetailShipment;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;


class ShipmentService
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function createShipment(Transaction $transaction, array $dataOngkir)
    {
        try {
            Log::info('Creating shipment for transaction: ' . $transaction->kode_transaksi);
            Log::info('Data Ongkir:', $dataOngkir);

            $existingShipment = Shipment::where('kode_transaksi', $transaction->kode_transaksi)->first();
            if ($existingShipment) {
                return;
            }

            $groupedByUser = $transaction->detail_transaction->groupBy(fn($item) => $item->product->user_id);

            foreach ($groupedByUser as $userId => $details) {
                $tokoId = $details->first()->product->user->toko->id ?? null;
                if (!$tokoId) {
                    Log::warning("No toko_id found for user_id {$userId}");
                    continue;
                }

                $ongkirForToko = collect($dataOngkir)->firstWhere('toko_id', $tokoId);
                if (!$ongkirForToko) {
                    Log::warning("No ongkir found for toko_id {$tokoId}");
                    continue;
                }

                $shipment = Shipment::create([
                    'kode_transaksi' => $transaction->kode_transaksi,
                    'kurir' => $ongkirForToko['kurir'],
                    'ongkir' => $ongkirForToko['ongkir'],
                    'status_pengiriman' => 'belum dibayar',
                ]);

                Log::info('Created shipment with ID: ' . $shipment->id);

                foreach ($details as $detail) {
                    DetailShipment::create([
                        'id_shipment' => $shipment->id,
                        'detail_transaksi_id' => $detail->id,
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('Error creating shipment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
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

        $trackingInfo = $this->rajaOngkir->trackShipment($shipment->kode_resi, $shipment->kurir);

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
