<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\DetailTransaction\StoreRequest;
use App\Http\Requests\DetailTransaction\UpdateRequest;

class DetailTransactionController extends Controller
{
    public function index(Request $request)
    {
        // nampilin detail transaksi beserta info produk dan tokonya
        $detailTransaction = DetailTransaction::with('product.user.toko')
            ->filter($request)
            ->paginate(10);

        if ($detailTransaction->isEmpty()) {
            return response()->json([
                'message' => 'Detail Transaction Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Transaction details retrieved successfully',
            'data' => $detailTransaction
        ], 200);
    }

    public function store(StoreRequest $request)
    {
        // nambahin rincian barang ke suatu transaksi dan ngitung ulang total harga serta berat transaksi
        $product = Product::find($request->product_id);
        $harga = $product->harga;
        $subtotal = $harga * $request->jumlah;
        $totalberat = $product->berat * $request->jumlah;
        $transaction = Transaction::findOrFail($request->transaction_id);

        $detailTransaction = new DetailTransaction();
        $detailTransaction->transaction_id = $request->transaction_id;
        $detailTransaction->product_id = $request->product_id;
        $detailTransaction->harga = $harga;
        $detailTransaction->jumlah = $request->jumlah;
        $detailTransaction->subtotal = $subtotal;
        $detailTransaction->totalberat = $totalberat;
        $detailTransaction->save();

        $total_harga = $transaction->detail_transaction->sum('subtotal');
        $total_berat = $transaction->detail_transaction->sum('totalberat');
        $transaction->total_harga = $total_harga;
        $transaction->total_berat = $total_berat;
        $transaction->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Transaction detail added successfully',
            'data' => $detailTransaction
        ], 201);
    }

    public function update(UpdateRequest $request, DetailTransaction $detailTransaction)
    {
        // ngubah detail pesanan, ngitung ulang subtotal, dan update total belanjaan di transaksi
        $product = Product::find($request->product_id);
        $harga = $product->harga;
        $totalberat = $product->berat * $request->jumlah;
        $subtotal = $harga * $request->jumlah;
        $transaction = Transaction::findOrFail($request->transaction_id);

        $detailTransaction->transaction_id = $request->transaction_id;
        $detailTransaction->product_id = $request->product_id;
        $detailTransaction->harga = $harga;
        $detailTransaction->jumlah = $request->jumlah;
        $detailTransaction->subtotal = $subtotal;
        $detailTransaction->totalberat = $totalberat;
        $detailTransaction->save();

        $total_harga = $transaction->detail_transaction->sum('subtotal');
        $total_berat = $transaction->detail_transaction->sum('totalberat');
        $transaction->total_harga = $total_harga;
        $transaction->total_berat = $total_berat;
        $transaction->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Transaction detail updated successfully',
            'data' => $detailTransaction
        ]);
    }

    public function delete(DetailTransaction $detailTransaction)
    {
        // ngapus detail pesanan dan otomatis ngurangin total harga dari transaksi utamanya
        if ($detailTransaction->transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $transaction = Transaction::findOrFail($detailTransaction->transaction_id);
        $detailTransaction->delete();
        $total_harga = $transaction->detail_transaction->sum('subtotal');
        $transaction->total_harga = $total_harga;
        $transaction->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
