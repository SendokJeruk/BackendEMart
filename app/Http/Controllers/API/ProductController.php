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
    public function index()
    {
        try {
            $products = Product::paginate(10);
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
                'category_id' => 'required',
                'nama_product' => 'required',
                'deskripsi' => 'required',
                'harga' => 'required',
                'stock' => 'required',
                'foto' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();
            $data['foto'] = $this->upload->save($request->file('foto'));
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

    public function edit(Request $request, Product $product) {
        try {
            $validate = Validator::make($request->all(), [
                'user_id' => 'required',
                'category_id' => 'required',
                'nama_product' => 'required',
                'deskripsi' => 'required',
                'harga' => 'required',
                'stock' => 'required',
                'foto' => 'nullable',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = $request->all();

            if($request->file('foto')) {
                $data['foto'] = $this->upload->update($product->foto, $request->file('foto'));
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
            $this->upload->delete($product->foto);
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
