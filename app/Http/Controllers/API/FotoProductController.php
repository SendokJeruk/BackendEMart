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

    public function index()
    {

        $fotoProduct = FotoProduct::paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Product photo data retrieved successfully',
            'data' => $fotoProduct
        ]);
    }
    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'foto_id'    => 'required',
            'product_id' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $fotoProduct = new FotoProduct();
        $fotoProduct->foto_id = $request->input('foto_id');
        $fotoProduct->product_id = $request->input('product_id');
        $fotoProduct->save();
        return response()->json([
            'status' => 'Success',
            'message' => 'Product photo added successfully',
            'data' => $fotoProduct
        ],201);

    }

    public function update(Request $request, FotoProduct $fotoProduct)
    {

        $validate = Validator::make($request->all(), [
            'foto_id'    => 'nullable',
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
            'status' => 'Success',
            'message' => 'Product photo updated successfully',
            'data' => $fotoProduct
        ], 200);

    }

    public function delete(FotoProduct $fotoProduct)
    {

        $fotoProduct->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);

    }

}
