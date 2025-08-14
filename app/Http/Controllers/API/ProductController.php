<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadRepository();
    }

    public function index(Request $request)
    {
        try {

            $products = Product::with(['categories', 'user.toko', 'foto', 'rating'])
                ->withAvg('rating', 'rating')
                ->filter($request)
                ->paginate(10);

            $products->getCollection()->transform(function ($product) {
                $product->average_rating = round($product->rating_avg_rating, 1);
                unset($product->rating_avg_rating);
                return $product;
            });

            return response()->json([
                'message' => 'Berhasil Dapatkan Data Produk',
                'data' => $products
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_product' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'harga' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'berat' => 'required|numeric|min:0',
                'foto_cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status_produk' => 'required|in:draft,publish',
            ]);

            $data = $request->only([
                'nama_product',
                'deskripsi',
                'harga',
                'stock',
                'berat',
                'status_produk'
            ]);

            $data['foto_cover'] = $this->upload->save($request->file('foto_cover'));
            $data['user_id'] = auth()->id();

            $product = Product::create($data);

            return response()->json([
                'message' => 'Berhasil Menambahkan Produk',
                'data' => $product
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function edit(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'nama_product' => 'nullable|string|max:255',
                'deskripsi' => 'nullable|string',
                'harga' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'berat' => 'nullable|numeric|min:0',
                'foto_cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status_produk' => 'nullable|in:draft,publish',
            ]);

            if ($request->hasFile('foto_cover')) {
                $validated['foto_cover'] = $this->upload->update($product->foto_cover, $request->file('foto_cover'));
            }

            $validated['user_id'] = auth()->id();

            $product->update($validated);

            return response()->json([
                'message' => 'Berhasil Edit Produk',
                'data' => $product->fresh()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Product $product)
    {
        try {
            $this->upload->delete($product->foto_cover);
            $product->delete();
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
