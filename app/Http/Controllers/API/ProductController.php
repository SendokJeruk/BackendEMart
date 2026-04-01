<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repository\UploadRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class ProductController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // Inisialisasi class UploadRepository jadi object di $this->upload biar gampang dipakai buat urusan upload gambar
        $this->upload = new UploadRepository();
    }

    public function index(Request $request)
    {
        // Ngambil semua data produk sekalian tarik data relasinya (kategori, toko, rating, foto)
        $products = Product::with(['categories', 'user.toko', 'rating', 'foto'])
            ->withAvg('rating', 'rating') // Hitung rata-rata ratingnya dari tabel rating
            ->filter($request) // Terapin filter misal pencarian dll
            ->paginate(10); // Bagi datanya per 10 item (pagination)

        // Rapihin format rata-rata ratingnya (misal dari 4.5000 jadi 4.5)
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
        // Khusus buat ngambil daftar produk punya si user (seller) yang lagi login
        $products = Product::with(['categories', 'rating', 'foto'])
            ->withAvg('rating', 'rating')
            ->where('user_id', auth()->id()) // Pastiin cuma ngambil milik yang login
            ->filter($request)
            ->paginate(10);

        // Rapihin format rata-rata ratingnya kayak di method index
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

    public function store(Request $request)
    {
        // Validasi inputan dari user, pastiin datanya lengkap dan formatnya bener (khususnya buat foto)
        $request->validate([
            'nama_product' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'berat' => 'required|numeric|min:0',
            'foto_cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status_produk' => 'required|in:draft,publish',
        ]);

        // Ambil data-data text nya aja dulu
        $data = $request->only([
            'nama_product',
            'deskripsi',
            'harga',
            'stock',
            'berat',
            'status_produk'
        ]);

        // Upload fotonya pake UploadRepository trus simpen nama filenya
        $data['foto_cover'] = $this->upload->save($request->file('foto_cover'));
        // Set kepemilikan produk ini ke user yang lagi login
        $data['user_id'] = auth()->id();

        // Simpan deh semua datanya ke database
        $product = Product::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Product added successfully',
            'data' => $product
        ], 201);
    }

    public function edit(Request $request, Product $product)
    {
        // Keamanan: Cek dulu nih, beneran nggak yang mau ngedit itu yang punya produknya?
        if ($product->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        // Validasi lagi, bedanya disini boleh kosong (nullable) kalau misal ada field yang gak mau diubah
        $validated = $request->validate([
            'nama_product' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'berat' => 'nullable|numeric|min:0',
            'foto_cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status_produk' => 'nullable|in:draft,publish',
        ]);

        // Kalau usernya upload gambar cover baru, kita ganti pake fungsi update di UploadRepository
        if ($request->hasFile('foto_cover')) {
            $validated['foto_cover'] = $this->upload->update($product->foto_cover, $request->file('foto_cover'));
        }

        // Pastiin lagi kepemilikannya gak berubah
        $validated['user_id'] = auth()->id();

        // Update datanya di database
        $product->update($validated);

        return response()->json([
            'status' => 'Success',
            'message' => 'Product updated successfully',
            'data' => $product->fresh()
        ]);
    }

    public function delete(Product $product)
    {
        // Keamanan: Cek lagi, yang ngehapus harus yang punya produk
        if ($product->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }

        // Hapus dulu file gambar cover dari penyimpanan server biar nggak menuh-menuhin disk
        $this->upload->delete($product->foto_cover);

        // Baru deh hapus data produknya dari database
        $product->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }

    public function getStatisticProduct()
    {
        // Ngambil 5 produk paling laris (terjual paling banyak) punya user yang login buat ditampilin misal di dashboard
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
