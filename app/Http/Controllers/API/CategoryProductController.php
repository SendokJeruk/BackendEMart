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

        $category_product = CategoryProduct::paginate(10);
        return response()->json([
            'message' => 'Berhasil Dapatkan Category Product',
            'data' => $category_product
        ]);

    }

    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'category_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validate->fails()) {
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

    }

    public function update(Request $request, CategoryProduct $category_product)
    {

        $validate = Validator::make($request->all(), [
            'category_id' => 'nullable',
            'product_id' => 'nullable',
        ]);

        if ($validate->fails()) {
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
            'message' => 'Category product telah di perbarui',
            'data' => $category_product
        ], 200);


    }

    public function delete(CategoryProduct $category_product)
    {

        $category_product->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);

    }
}

