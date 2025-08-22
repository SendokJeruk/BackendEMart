<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\income;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\DetailIncome;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Repository\SuccessPaymentRepository;

class TransactionController extends Controller
{
    protected $midtransService;

    protected $rajaOngkir;
    protected $successPayment;

    public function __construct(MidtransService $midtransService, RajaOngkirService $rajaOngkir, SuccessPaymentRepository $successPaymentRepository)
    {
        $this->midtransService = $midtransService;
        $this->rajaOngkir = $rajaOngkir;
        $this->successPayment = $successPaymentRepository;
    }

    public function createTransaction(Transaction $transaction, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'payment_type' => 'nullable',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $payment_type = [];
        $payment_type[] = $request->payment_type;

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

        // $origin = $request->origin; // ! ambil dari alamat toko -> olah data disini
        // $destination = $request->destination; //? get alamat user 4917
        // $weight = $transaction->total_berat;

        // $kurir = $this->rajaOngkir->getCost(
        //     $origin,
        //     $destination,
        //     $weight,
        //     "jnt",
        //     "lowest"
        // );

        $ongkir = $transaction->total_ongkir;

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
            !empty($payment_type) ?? 'enabled_payments' => $payment_type
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
                        Log::info('MASUK SETTLEMENT (CAPTURE) OTW IMPLEN KE DE BE');
                        $order->update(['status' => 'success']);
                        $this->successPayment->PaymentSuccess($orderId);
                    }
                }
                break;
            case 'settlement':
                Log::info('MASUK SETTLEMENT OTW IMPLEN KE DE BE');
                $this->successPayment->PaymentSuccess($orderId);
                $order->update(['status' => 'success']);

                // $order->load('detail_transaction.product.user');

                // foreach ($order->detail_transaction as $detail) {
                //     $product = $detail->product;

                //     if (!$product || !$product->user) {
                //         Log::warning("Produk atau seller tidak ditemukan untuk detail ID: {$detail->id}");
                //         continue;
                //     }

                //     $seller = $product->user;

                //     // Kurangi stok
                //     $product->stok = max(0, $product->stok - $detail->qty);
                //     $product->save();

                //     // Cari atau buat Income berdasarkan user_id
                //     $income = Income::firstOrCreate(
                //         ['user_id' => $seller->id],
                //         ['jumlah_total' => 0]
                //     );

                //     Log::info("Income ID yang didapat:", [
                //         'user_id' => $seller->id,
                //         'income_id' => $income->id ?? 'null'
                //     ]);

                //     // Hitung subtotal
                //     $subtotal = $detail->subtotal ?? ($detail->qty * $detail->harga);

                //     // Buat detail income
                //     $detailIncome = DetailIncome::create([
                //         'income_id' => $income->id,
                //         'detail_transaction_id' => $detail->id,
                //         'jumlah' => $subtotal
                //     ]);

                //     Log::info("DetailIncome ID created:", [
                //         'detail_income_id' => $detailIncome->id ?? 'null'
                //     ]);

                //     // Update jumlah_total income
                //     $income->increment('jumlah_total', $subtotal);
                // }
                //todo : inome
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

    public function getAllTransaction()
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

    public function index()
    {
        try {
            $user = Auth::user();
            $transaction = $user->transaction()->with('detail_transaction.product.user.toko.alamatToko')->paginate(5);
            return response()->json([
                'message' => 'Berhasil Menampilkan transaksi ' . $user->name,
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTransactionDetail(Transaction $transaction)
    {
        try {
            if ($transaction->user_id != auth()->id()) {
                return response()->json([
                    'message' => 'Forbidden'
                ], 403);
            }

            $result = [];

            foreach ($transaction->detail_transaction as $detail) {
                $group = $detail->product->user->toko->alamatToko->kode_domestik;

                if (!isset($result[$group])) {
                    $result[$group] = [];
                }

                $result[$group][] = [
                    'product_id' => $detail->product_id,
                    'harga' => $detail->harga,
                    'jumlah' => $detail->jumlah,
                    'subtotal' => $detail->subtotal,
                    'totalberat' => $detail->totalberat,
                ];
            }

            return response()->json([
                'message' => 'Berhasil mendapatkan detail dari transaksi ' . $transaction->kode_transaksi,
                'data' => $result
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
                'status' => 'nullable|string',
                'total_ongkir' => 'nullable|numeric'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            if ($request->has('total_ongkir')) {
                $transaction->total_ongkir = $request->input('total_ongkir');
                $transaction->total_harga = $transaction->detail_transaction->sum('subtotal') + $transaction->total_ongkir;
            }

            if ($request->filled('status')) {
                $transaction->status = $request->input('status');
            }

            $transaction->save();

            return response()->json([
                'message' => 'Berhasil Update transaksi',
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

    public function pesananMasuk(Request $request)
    {
        try {
            $sellerId = auth()->id(); // atau ambil dari $request->user_id

            $transactions = Transaction::whereHas('detail_transaction.product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId); // user_id adalah pemilik produk
            })
                ->with([
                    'detail_transaction' => function ($q) use ($sellerId) {
                        $q->whereHas('product', function ($query) use ($sellerId) {
                            $query->where('user_id', $sellerId);
                        })->with('product');
                    },
                    'user'
                ])
                ->latest()
                ->paginate(10);


            return response()->json([
                'message' => 'Berhasil menampilkan pesanan masuk',
                'data' => $transactions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
