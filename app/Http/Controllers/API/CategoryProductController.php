<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use App\Models\CategoryProduct;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\CategoryProduct\StoreRequest;
use App\Http\Requests\CategoryProduct\UpdateRequest;

class CategoryProductController extends Controller
{
    public function index()
    {
        // nampilin daftar relasi kategori sama produk khusus buat produk punya user yang login
        $category_product = CategoryProduct::with([
            'category:id,nama_category',
            'product:id,nama_product,user_id'
        ])->whereHas('product', function ($query) {
            $query->where('user_id', auth()->id());
        })->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Product categories retrieved successfully',
            'data' => $category_product
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngehubungin kategori tertentu ke suatu produk, pastiin produknya punya si user
        Log::info("BUDI MEMANGGIL KATEGORI PRODUK LE");
        Log::info($request->all());

        $product = Product::findOrFail($request->product_id);

        Log::info("BUDI MENCOBA SIMPAN KATEGORI PRODUK");
        $product->categories()->syncWithoutDetaching([$request->category_id]);
        Log::info("SUKSES SIMPAN KATEGORI KE PRODUK");

        return response()->json([
            'status' => 'Success',
            'message' => 'Data created successfully',
            'data' => $product->load('categories')
        ], 201);
    }

    public function update(UpdateRequest $request, CategoryProduct $category_product)
    {
        // ngubah relasi kategori di suatu produk
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
        // ngapus relasi antara kategori dan produk
        $category_product->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
