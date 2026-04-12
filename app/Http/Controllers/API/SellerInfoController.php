<?php

namespace App\Http\Controllers\API;


use App\Models\Income;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SellerInfoController extends Controller
{
        public function topsellers($limit = 5)
    {
            // ngurutin seller berdasarkan total penjualannya trus dibalikin data top sellernya
    $sellers = Income::with('user')
        ->orderByDesc('total_penjualan')
        ->take($limit)
        ->get();

    $data = $sellers->map(function ($seller) {
        return [
           'seller' => ($seller->user->toko->nama_toko ?? 'Toko') . ' - ' . $seller->user->name,
            'penjualan_total' => $seller->total_penjualan ?? 0,
            'income' => $seller->jumlah_total ?? 0,
        ];
    });
        return response()->json([
                'status' => 'Success',
                'message' => 'SellerInfo retrieved successfully',
                'data' => $data
         ]);

}
}
