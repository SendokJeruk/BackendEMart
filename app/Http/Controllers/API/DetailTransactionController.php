<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class DetailTransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DetailTransaction::query();

            if ($request->has('transaction_id')) {
                $query->where('transaction_id', $request->transaction_id);
            }

            $detailTransaction = $query->paginate(10);

            if ($detailTransaction->isEmpty()) {
                return response()->json([
                    'message' => 'Detail transaksi tidak ditemukan',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Berhasil Menampilkan detail transaksi',
                'data' => $detailTransaction
            ], 200);
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
                'transaction_id' => 'required',
                'product_id' => 'required',
                'jumlah' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $product = Product::find($request->product_id);
            $harga = $product->harga;
            $subtotal = $harga * $request->jumlah;
            $transaction = Transaction::findOrFail($request->transaction_id);

            $detailTransaction = new DetailTransaction();
            $detailTransaction->transaction_id = $request->transaction_id;
            $detailTransaction->product_id = $request->product_id;
            $detailTransaction->harga = $harga;
            $detailTransaction->jumlah = $request->jumlah;
            $detailTransaction->subtotal = $subtotal;
            $detailTransaction->save();

            $total_harga = $transaction->detail_transaction->sum('subtotal');
            $transaction->total_harga = $total_harga;
            $transaction->save();

            return response()->json([
                'message' => 'Berhasil menambahkan data Detail transaksi',
                'data' => $detailTransaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, DetailTransaction $detailTransaction)
    {
        try {
            $validate = Validator::make($request->all(), [
                'transaction_id' => 'nullable',
                'product_id' => 'nullable',
                'jumlah' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $product = Product::find($request->product_id);
            $harga = $product->harga;
            $subtotal = $harga * $request->jumlah;
            $transaction = Transaction::findOrFail($request->transaction_id);

            $detailTransaction->transaction_id = $request->transaction_id;
            $detailTransaction->product_id = $request->product_id;
            $detailTransaction->harga = $harga;
            $detailTransaction->jumlah = $request->jumlah;
            $detailTransaction->subtotal = $subtotal;
            $detailTransaction->save();

            $total_harga = $transaction->detail_transaction->sum('subtotal');
            $transaction->total_harga = $total_harga;
            $transaction->save();

            return response()->json([
                'message' => 'Berhasil Edit Detail transaksi',
                'data' => $detailTransaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete(DetailTransaction $detailTransaction)
    {
        try {
            $transaction = Transaction::findOrFail($detailTransaction->transaction_id);
            $detailTransaction->delete();
            $total_harga = $transaction->detail_transaction->sum('subtotal');
            $transaction->total_harga = $total_harga;
            $transaction->save();

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
