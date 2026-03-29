<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class CategoryProductController extends Controller
{
    public function index()
    {

        $category_product = CategoryProduct::with([
            'category:id,nama_category',
            'product:id,nama_product,user_id'
            ])->whereHas('product', function ($query) {
                $query->where('user_id', auth()->id());
            })->get();

        return response()->json([
            'status' => 'Success',
            'message' => 'Product categories retrieved successfully',
            'data' => $category_product
        ]);
    }

    public function store(Request $request)
    {
        Log::info("BUDI MEMANGGIL KATEGORI PRODUK LE");
        Log::info($request->all());

        $product = Product::findOrFail($request->product_id);

        if ((int)$product->user_id !== (int)auth()->id()) {
            throw new AuthorizationException();
        }

        $validate = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        Log::info("BUDI MENCOBA SIMPAN KATEGORI PRODUK");

        $product->categories()->syncWithoutDetaching([$request->category_id]);

        Log::info("SUKSES SIMPAN KATEGORI KE PRODUK");

        return response()->json([
            'status' => 'Success',
            'message' => 'Data created successfully',
            'data' => $product->load('categories')
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

