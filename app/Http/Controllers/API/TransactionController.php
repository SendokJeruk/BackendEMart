<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(){
        try {
            $transaction = Transaction::paginate(10);
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

    public function store(Request $request){
        try {
            $validate = Validator::make($request->all(),[
                'user_id' => 'required',
                'total_harga' => 'required',
                'status' => 'required',
                'tanggal_transaksi' => 'nullable',
                'kode_transaksi' => 'nullable',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);

            }
            $transaction = new Transaction();
            $transaction->user_id = $request->user_id;
            $transaction->total_harga = $request->total_harga;
            $transaction->status = $request->status;
            $transaction->tanggal_transaksi = $request->tanggal_transaksi ?? now();
            $transaction->kode_transaksi = $request->kode_transaksi ?? 'ID-' . time() . strtoupper(Str::random(5));
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

    public function update(Request $request, Transaction $transaction) {
        try {
            $validate = Validator::make($request->all(),[
                'user_id' => 'required',
                'total_harga' => 'required',
                'status' => 'required',
                'tanggal_transaksi' => 'nullable',
                'kode_transaksi' => 'nullable',
            ]);

            if($validate->fails()) {
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

    public function delete(Transaction $transaction) {
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
