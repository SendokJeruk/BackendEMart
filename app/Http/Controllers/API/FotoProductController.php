<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\FotoProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\FotoProduct\StoreRequest;
use App\Http\Requests\FotoProduct\UpdateRequest;

class FotoProductController extends Controller
{
    public function index()
    {
        // ngambil semua daftar foto-foto produk
        $fotoProduct = FotoProduct::paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Product photo data retrieved successfully',
            'data' => $fotoProduct
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngehubungin foto yang udah diupload ke suatu produk, cek dulu produknya punya user
        $fotoProduct = new FotoProduct();
        $fotoProduct->foto_id = $request->input('foto_id');
        $fotoProduct->product_id = $request->input('product_id');
        $fotoProduct->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Product photo added successfully',
            'data' => $fotoProduct
        ], 201);
    }

    public function update(UpdateRequest $request, FotoProduct $fotoProduct)
    {
        // ngubah relasi foto buat suatu produk
        $fotoProduct->update([
            'foto_id' => $request->foto_id,
            'product_id' => $request->product_id
        ]);
        return response()->json([
            'status' => 'Success',
            'message' => 'Product photo updated successfully',
            'data' => $fotoProduct
        ], 200);
    }

    public function delete(FotoProduct $fotoProduct)
    {
        // ngapus kaitan foto dari produk, pastiin cuma pemilik produk yang bisa hapus
        if ($fotoProduct->product->user_id !== auth()->id()) {
            throw new AuthorizationException();
        }
        $fotoProduct->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
