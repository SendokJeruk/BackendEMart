<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;

class ProductController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadRepository();
    }

    public function index(Request $request)
    {
        // nampilin semua produk dengan relasinya sekalian hitung rata-rata ratingnya
        $products = Product::with(['categories', 'user.toko', 'rating', 'foto'])
            ->withAvg('rating', 'rating')
            ->filter($request)
            ->paginate(10);

        $products->getCollection()->transform(function ($product) {
            $product->average_rating = round($product->rating_avg_rating, 1);
            unset($product->rating_avg_rating);
            return $product;
        });

        return response()->json([
            'status' => 'Success',
            'message' => 'Product data retrieved successfully',
            'data' => $products
        ]);
    }

    public function getMyProducts(Request $request)
    {
        // ngambil produk-produk punya user yang lagi login aja, buat di dashboard seller
        $products = Product::with(['categories', 'rating', 'foto'])
            ->withAvg('rating', 'rating')
            ->where('user_id', auth()->id())
            ->filter($request)
            ->paginate(10);

        $products->getCollection()->transform(function ($product) {
            $product->average_rating = round($product->rating_avg_rating, 1);
            unset($product->rating_avg_rating);
            return $product;
        });

        return response()->json([
            'status' => 'Success',
            'message' => 'Product data retrieved successfully',
            'data' => $products
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngupload foto cover produk, set owner ke user login, trus simpen data produknya
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
            'status' => 'Success',
            'message' => 'Product added successfully',
            'data' => $product
        ], 201);
    }

    public function edit(UpdateRequest $request, Product $product)
    {
        // ngecek kepemilikan produk, ngupdate datanya, dan ganti foto cover kalo user upload yang baru
        $validated = $request->validated();

        if ($request->hasFile('foto_cover')) {
            $validated['foto_cover'] = $this->upload->update($product->foto_cover, $request->file('foto_cover'));
        }

        $validated['user_id'] = auth()->id();

        $product->update($validated);

        return response()->json([
            'status' => 'Success',
            'message' => 'Product updated successfully',
            'data' => $product->fresh()
        ]);
    }

    public function delete(Product $product)
    {
        // pastiin ini produk milik user, hapus foto cover dari storage, lalu hapus produk dari database
        if ($product->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        $this->upload->delete($product->foto_cover);

        $product->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }

    public function getStatisticProduct()
    {
        // ngambil 5 produk paling laris (terjual paling banyak) punya seller
        $products = Product::orderByDesc('terjual')
            ->take(5)
            ->where('user_id', auth()->id())
            ->get(['id', 'nama_product', 'terjual']);

        return response()->json([
            'status' => 'Success',
            'message' => 'Top 5 products retrieved successfully',
            'data' => $products
        ]);
    }
}
