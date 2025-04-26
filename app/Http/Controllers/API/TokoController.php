<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TokoController extends Controller
{
    public function index(){
        try {
            $toko = Toko::paginate(10);
            return response()->json([
                'message' => 'Berhasil Dapatkan Data toko',
                'data' => $toko
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
                'nama_toko' => 'required',
                'deskripsi' => 'required',
                'no_telp' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = User::find($request->user_id);
            if ($user->toko) {
                return response()->json([
                    'message' => 'User sudah memiliki toko',
                ], 409);
            }

            $toko = new Toko();
            $toko->user_id = $request->user_id;
            $toko->nama_toko = $request->input('nama_toko');
            $toko->deskripsi = $request->input('deskripsi');
            $toko->no_telp = $request->input('no_telp');
            $toko->save();
            return response()->json([
                'message' => 'Toko telah terbuat',
                'data' => $toko
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Toko $toko){
        try {
            $validate = Validator::make($request->all(),[
                'user_id' => 'nullable',
                'nama_toko' => 'nullable',
                'deskripsi' => 'nullable',
                'no_telp' => 'nullable',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $toko->update([
                'user_id' => $request->user_id,
                'nama_toko' => $request->nama_toko,
                'deskripsi' => $request->deskripsi,
                'no_telp' => $request->no_telp
            ]);

            return response()->json([
                'message' => 'Toko telah diperbarui',
                'data' => $toko
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Toko $toko){
        try {
            $toko->delete();

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
