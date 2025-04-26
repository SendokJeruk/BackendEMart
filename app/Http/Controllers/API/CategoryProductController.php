<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryProductController extends Controller
{
    public function index()
    {
        try {
            $category_product = CategoryProduct::paginate(10);
            return response()->json([
                'message' => 'Berhasil Dapatkan Data Category Product',
                'data' => $category_product
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
                'category_id' => 'required',
                'product_id' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }
            $category_product = new CategoryProduct();
            $category_product->category_id = $request->input('category_id');
            $category_product->product_id = $request->input('product_id');
            $category_product->save();
            return response()->json([
                'message' => 'data telah di tambahkan',
                'data' => $category_product
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, CategoryProduct $category_product){
        try {
            $validate = Validator::make($request->all(),[
                'category_id' => 'nullable',
                'product_id' => 'nullable',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $category_product->update([
                'category_id' => $request->category_id,
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'message' => 'Category telah di perbarui',
                'data' => $category_product
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(CategoryProduct $category_product){
        try {
            $category_product->delete();

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

