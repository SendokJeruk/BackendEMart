<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\FotoProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use Illuminate\Support\Facades\Validator;

class FotoProductController extends Controller
{

    public function index(){
       try {
        $fotoProduct = FotoProduct::paginate(10);
        return response()->json([
            'message' => 'Berhasil Dapatkan Data FotoProduct',
            'data' => $fotoProduct
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
            $validate = Validator::make($request->all(), [
                'foto_id' => 'required',
                'product_id' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $fotoProduct= new FotoProduct();
            $fotoProduct->foto_id = $request->input('foto_id');
            $fotoProduct->product_id = $request->input('product_id');
            $fotoProduct->save();
            return response()->json([
                'message' => 'Berhasil Menambahkan FotoProduct',
                'data' => $fotoProduct
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, FotoProduct $fotoProduct){
        try {
                 $validate = Validator::make($request->all(), [
                    'foto_id' => 'nullable',
                    'product_id' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $fotoProduct->update([
                'foto_id' => $request->foto_id,
                'product_id' => $request->product_id
            ]);
            return response()->json([
                'message' => 'Category Updated',
                'data' => $fotoProduct
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(FotoProduct $fotoProduct){
        try {
            $fotoProduct->delete();
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
