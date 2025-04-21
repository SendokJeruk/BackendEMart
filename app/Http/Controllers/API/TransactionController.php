<?php

namespace App\Http\Controllers\API;

use Exception;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function createTransaction(Transaction $transaction)
    {
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

        $order_id = $transaction->kode_transaksi;

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
            'item_details' => $products
        ];

        // return response()->json($params);

        $result = $this->midtransService->createTransaction($params);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json([
            'snap_token' => $result['snap_token'],
            'redirect_url' => $result['redirect_url']
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
                'user_id' => 'required',
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
            $transaction = new Transaction();
            $transaction->user_id = $request->user_id;
            $transaction->status = $request->status;
            $transaction->tanggal_transaksi = $request->tanggal_transaksi ?? now();
            $transaction->kode_transaksi = $request->kode_transaksi ?? 'SJK-' . time() . strtoupper(Str::random(5));
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
                'user_id' => 'required',
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
