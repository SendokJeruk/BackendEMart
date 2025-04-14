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

            $query = Product::query();

            if ($request->has('nama_product')) {
                $query->where('nama_product', 'like', "%{$request->nama_product}%");
            }

            $products = $query->paginate(10);

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

    public function draft()
    {
        try {
            $products = Product::where('status_produk', 'draft')->paginate(10);
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
            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
                'nama_product' => 'required',
                'deskripsi' => 'required',
                'harga' => 'required',
                'stock' => 'required',
                'foto_cover' => 'required',
                'status_produk' => 'required|in:draft,publish',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();
            $data['foto_cover'] = $this->upload->save($request->file('foto_cover'));
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
            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
                'nama_product' => 'required',
                'deskripsi' => 'required',
                'harga' => 'required',
                'stock' => 'required',
                'foto_cover' => 'nullable',
                'status_produk' => 'required|in:draft,publish',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();

            if ($request->file('foto_cover')) {
                $data['foto_cover'] = $this->upload->update($product->foto_cover, $request->file('foto_cover'));
            }

            $product->update($data);

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
