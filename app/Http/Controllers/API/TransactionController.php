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
use App\Repository\SuccessPaymentRepository;
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\TransactionService;
use App\Http\Requests\Transaction\CreateTransactionRequest;
use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;

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
        // ngejalanin fungsi __construct
        $this->midtransService = $midtransService;
        $this->rajaOngkir = $rajaOngkir;
        $this->successPayment = $successPaymentRepository;
        $this->shipment = $shipment;
        $this->transactionService = $transactionService;
    }

    public function createTransaction(Transaction $transaction, CreateTransactionRequest $request)
    {
        // minta token pembayaran sama URL dari Midtrans buat checkout
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
        // ngambil daftar seluruh transaksi buat dipantau admin
        $transaction = Transaction::with('detail_transaction')->paginate(10);
        return response()->json([
            'message' => 'Berhasil Menampilkan transaksi',
            'data' => $transaction
        ]);
    }

    public function index()
    {
        // ngambil daftar transaksi beserta detail barang n tokonya buat user login
        $user = Auth::user();
        $transaction = $user->transaction()->with('detail_transaction.product.user.toko.alamatToko')->paginate(5);
        return response()->json([
            'message' => 'Berhasil Menampilkan transaksi ' . $user->name,
            'data' => $transaction
        ]);
    }

    public function getTransactionDetail(Transaction $transaction)
    {
        // ngambil rincian pesanan di satu transaksi trus di-grouping per toko
        if ($transaction->user_id != auth()->id()) {
            throw new AuthorizationException();
        }

        $result = [];

        foreach ($transaction->detail_transaction as $detail) {
            $toko = $detail->product->user->toko;
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

    public function store(StoreRequest $request)
    {
        // bikin data transaksi awal (mentah) saat proses checkout berjalan
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

    public function update(UpdateRequest $request, Transaction $transaction)
    {
        // ngupdate ongkos kirim atau status transaksi n ngitung total akhirnya
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
        // ngapus transaksi secara keseluruhan, pastinya ngecek akses usernya dulu
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
        // nampilin transaksi masuk yang isinya produk jualan si seller
        $sellerId = auth()->id();

        $transactions = Transaction::whereHas('detail_transaction.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
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
