<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\ShipmentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $midtransService;
    protected $shipmentService;

    public function __construct(MidtransService $midtransService, ShipmentService $shipmentService)
    {
        $this->midtransService = $midtransService;
        $this->shipmentService = $shipmentService;
    }

    public function createPayment(Transaction $transaction, array $dataOngkir, ?string $paymentType = null)
    {
        return DB::transaction(function () use ($transaction, $dataOngkir, $paymentType) {
            $transaction->load('detail_transaction.product', 'user');

            $products = $transaction->detail_transaction->map(function ($detail) {
                return [
                    'id' => $detail->product->id,
                    'name' => $detail->product->nama_product,
                    'price' => (int) $detail->product->harga,
                    'quantity' => $detail->jumlah,
                ];
            })->toArray();

            $ongkir = $transaction->total_ongkir;

            array_push($products, [
                'id' => 'ONGKIR',
                'name' => 'Biaya Ongkir',
                'price' => (int) $ongkir,
                'quantity' => 1,
            ]);

            $transaction->payment_attempt += 1;
            $orderId = $transaction->kode_transaksi . 'ATTEMPT' . str_pad($transaction->payment_attempt, 2, '0', STR_PAD_LEFT);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $transaction->total_harga,
                ],
                'customer_details' => [
                    'first_name' => $transaction->user->name,
                    'email' => $transaction->user->email,
                    'phone' => $transaction->user->no_telp,
                ],
                'item_details' => $products,
            ];

            if ($paymentType) {
                $params['enabled_payments'] = [$paymentType];
            }

            $result = $this->midtransService->createTransaction($params);

            if (isset($result['error'])) {
                throw new \Exception($result['error']);
            }

            $transaction->save();
            $this->shipmentService->createShipment($transaction, $dataOngkir);

            return [
                'payment_attempt' => $transaction->payment_attempt,
                'snap_token' => $result['snap_token'],
                'redirect_url' => $result['redirect_url']
            ];
        });
    }
}
