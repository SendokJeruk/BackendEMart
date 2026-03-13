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
use App\Services\ShipmentService;
use Illuminate\Support\Facades\DB;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Repository\SuccessPaymentRepository;
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    protected $midtransService;
    protected $rajaOngkir;
    protected $successPayment;
    protected $shipment;
    protected $transactionService;

    public function __construct(
        MidtransService $midtransService,
        RajaOngkirService $rajaOngkir,
        SuccessPaymentRepository $successPaymentRepository,
        ShipmentService $shipment,
        TransactionService $transactionService
    ) {
        $this->midtransService = $midtransService;
        $this->rajaOngkir = $rajaOngkir;
        $this->successPayment = $successPaymentRepository;
        $this->shipment = $shipment;
        $this->transactionService = $transactionService;
    }

    public function createTransaction(Transaction $transaction, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'payment_type' => 'nullable',
            'data_ongkir' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        if ($transaction->status == 'success') {
            return response()->json([
                'message' => 'Transaksi sudah berhasil, silahkan cek riwayat transaksi anda'
            ], 400);
        }

        try {
            $result = $this->transactionService->createPayment(
                $transaction,
                $request->data_ongkir,
                $request->payment_type
            );

            return response()->json([
                'message' => 'Payment Berhasil Dibuat',
                'payment_attempt' => $result['payment_attempt'],
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url']
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getAllTransaction()
    {

        $transaction = Transaction::with('detail_transaction')->paginate(10);
        return response()->json([
            'message' => 'Berhasil Menampilkan transaksi',
            'data' => $transaction
        ]);

    }

    public function index()
    {

        $user = Auth::user();
        $transaction = $user->transaction()->with('detail_transaction.product.user.toko.alamatToko')->paginate(5);
        return response()->json([
            'message' => 'Berhasil Menampilkan transaksi ' . $user->name,
            'data' => $transaction
        ]);

    }

    public function getTransactionDetail(Transaction $transaction)
    {
        if ($transaction->user_id != auth()->id()) {
            throw new AuthorizationException();
        }

        $result = [];

        foreach ($transaction->detail_transaction as $detail) {
            $toko = $detail->product->user->toko;

            // pakai id toko biar unik
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
                'detail_transaction_id' => $detail->id,
                'nama_product' => $detail->product->nama_product,
                'foto_cover' => $detail->product->foto_cover,
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
    }



    public function store(Request $request)
    {

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

    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

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

    }


    public function delete(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);

    }

    public function pesananMasuk(Request $request)
    {

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

    }
}
