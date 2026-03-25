<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class DetailTransactionController extends Controller
{
    public function index(Request $request)
    {


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


    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'product_id' => 'required',
            'jumlah' => 'required|integer|min:1',
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

    public function update(Request $request, DetailTransaction $detailTransaction)
    {
        if ($detailTransaction->transaction->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $validate = Validator::make($request->all(), [
            'transaction_id' => 'nullable',
            'product_id' => 'nullable',
            'jumlah' => 'nullable|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

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
