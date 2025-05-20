<?php

namespace App\Http\Controllers\API;

use Exception;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $midtransService;

    protected $rajaOngkir;

    public function __construct(MidtransService $midtransService, RajaOngkirService $rajaOngkir)
    {
        $this->midtransService = $midtransService;
        $this->rajaOngkir = $rajaOngkir;
    }

    public function createTransaction(Transaction $transaction, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'origin' => 'required',
            'destination' => 'required',
            // 'payment_type' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        // $payment_type = [];
        // $payment_type[] = $request->payment_type;

        $transaction->load('detail_transaction.product');

        $products = $transaction->detail_transaction->map(function ($detail) {
            return [
                'id' => $detail->product->id,
                'name' => $detail->product->nama_product,
                'price' => $detail->product->harga,
                'quantity' => $detail->jumlah,
                // 'subtotal' => $detail->subtotal,
            ];
        })->toArray();

        $origin = $request->origin;
        $destination = $request->destination;
        $weight = $transaction->total_berat;

        $kurir = $this->rajaOngkir->getCost(
            $origin,
            $destination,
            $weight,
            "jnt",
            "lowest"
        );

        $ongkir = $kurir['data'][0]['cost'];

        array_push($products, [
            'id' => 999,
            'name' => 'Biaya Ongkir',
            'price' => $ongkir,
            'quantity' => 1,
        ]);

        $order_id = $transaction->kode_transaksi;
        $transaction->total_harga = $transaction->total_harga + $ongkir;
        $transaction->save();

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $transaction->total_harga,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
                'phone' => $transaction->user->no_telp
            ],
            'item_details' => $products,
            // 'enabled_payments' => $payment_type
        ];

        // return response()->json($params);

        $result = $this->midtransService->createTransaction($params);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'Payment Berhasil Dibuat',
            'data' => [
                'snap_token' => $result['snap_token'],
                'redirect_url' => $result['redirect_url']
            ]
        ]);
    }

    //! Callback masih bermasalah
    public function handleCallback(Request $request)
    {
        try {
            $notif = new Notification();

            $status = $notif->transaction_status;
            $order_id = $notif->order_id;
            $signature_key = $notif->signature_key;

            $expected_signature = hash('sha512', $notif->order_id . $notif->status_code . $notif->gross_amount . env('MIDTRANS_SERVER_KEY'));

            if ($signature_key != $expected_signature) {
                abort(400, 'Invalid signature');
            }

            if ($status == 'success') {
                $transaction = Transaction::where('kode_transaksi', $order_id)->first();
                $transaction->status = 'success';
                $transaction->save();
            } elseif ($status == 'pending') {
                $transaction = Transaction::where('kode_transaksi', $order_id)->first();
                $transaction->status = 'pending';
                $transaction->save();
            } elseif ($status == 'deny') {
                $transaction = Transaction::where('kode_transaksi', $order_id)->first();
                $transaction->status = 'failed';
                $transaction->save();
            }

            return response()->json(['status' => 'OK']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //? CALLBACK BARU
    public function callback(Request $request)
    {
        // $method = $request->input('_method') ?? $request->method();

        // if (strtoupper($method) !== 'PUT' && strtoupper($method) !== 'POST') {
        //     return response()->json(['message' => 'Invalid method'], 405);
        // }
        // Log semua data yang masuk untuk debug
        Log::info('Received callback: ', $request->all());

        // Cek jika semua parameter yang dibutuhkan ada
        $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
        foreach ($requiredFields as $field) {
            if (!$request->has($field)) {
                Log::error('Missing required field: ' . $field);
                return response('Missing required field: ' . $field, 400);
            }
        }

        // Ambil server key dari konfigurasi
        $serverKey = config('midtrans.server_key');

        // Cek signature
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // Log untuk debug signature key
        Log::info('Calculated Hash: ' . $hashedKey);
        Log::info('Received Signature Key: ' . $request->signature_key);

        // Jika signature tidak valid
        if ($hashedKey !== $request->signature_key) {
            Log::error('Invalid signature key for order ID: ' . $request->order_id);
            return response('Invalid signature key', 403);
        }

        // Ambil data transaksi
        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;

        // Cek jika order ditemukan
        $order = Transaction::where('kode_transaksi', $orderId)->first();

        if (!$order) {
            Log::error('Order not found: ' . $orderId);
            return response('Order not found', 404);
        }

        // Log status transaksi
        Log::info('Transaction Status: ' . $transactionStatus);

        // Handle status transaksi
        switch ($transactionStatus) {
            case 'capture':
                if ($request->payment_type == 'credit_card') {
                    if ($request->fraud_status == 'challenge') {
                        Log::info('Fraud challenge for order ID: ' . $orderId);
                        $order->update(['status' => 'pending']);
                    } else {
                        $order->update(['status' => 'success']);
                    }
                }
                break;
            case 'settlement':
                $order->update(['status' => 'success']);
                break;
            case 'pending':
                $order->update(['status' => 'pending']);
                break;
            case 'deny':
                $order->update(['status' => 'failed']);
                break;
            case 'expire':
                $order->update(['status' => 'expired']);
                break;
            case 'cancel':
                $order->update(['status' => 'canceled']);
                break;
            default:
                $order->update(['status' => 'unknown']);
                break;
        }

        // Log perubahan status order
        Log::info('Order updated for order ID: ' . $orderId . ' with status: ' . $order->status);

        // Kirim response OK ke Midtrans
        return response('OK', 200);
    }


    //? CALLBACK BARU
    //! Callback masih bermasalah

    public function index()
    {
        try {
            $transaction = Transaction::with('detail_transaction')->paginate(10);
            return response()->json([
                'message' => 'Berhasil Menampilkan transaksi',
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'status' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }
            $transaction = new Transaction();
            $transaction->user_id = auth()->id();
            $transaction->status = $request->status;
            $transaction->tanggal_transaksi = now();
            $transaction->kode_transaksi = 'SJK-' . time() . strtoupper(Str::random(5));
            $transaction->save();
            return response()->json([
                'message' => 'Berhasil menambahkan transaksi',
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Transaction $transaction)
    {
        try {
            $validate = Validator::make($request->all(), [
                'status' => 'required',
                'tanggal_transaksi' => 'nullable',
                'kode_transaksi' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();
            $data['user_id'] = auth()->id();
            $transaction->update($data);

            return response()->json([
                'message' => 'Berhasil Edit transaksi',
                'data' => $transaction->fresh()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Transaction $transaction)
    {
        try {
            $transaction->delete();

            return response()->json([
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
