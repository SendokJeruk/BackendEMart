<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class CategoryProductController extends Controller
{
    public function index()
    {

        $category_product = CategoryProduct::with([
            'category:id,nama_category',
            'product:id,nama_product'
        ])->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Product categories retrieved successfully',
            'data' => $category_product
        ]);


    }

    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        if ($product->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

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
            'status' => 'Success',
            'message' => 'Data created successfully',
            'data' => $category_product
        ], 201);

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
            'status' => 'Success',
            'message' => 'Product category updated successfully',
            'data' => $category_product
        ], 200);


    }

    public function delete(CategoryProduct $category_product)
    {

        $category_product->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data berhasil dihapus'
        ]);

    }
}

