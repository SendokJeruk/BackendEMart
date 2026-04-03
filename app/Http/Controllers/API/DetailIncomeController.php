<?php

namespace App\Http\Controllers\API;

use auth;
use Exception;
use App\Models\DetailIncome;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DetailIncomeController extends Controller
{
    public function index()
    {
        // ngambil riwayat rincian pendapatan dari transaksi yang udah beres, khusus buat seller
        $user = auth()->user();

        $detailIncomes = DetailIncome::with(['income', 'detailTransaksi.product'])
            ->whereHas('income', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => 'Success',
            'message' => 'Income data retrieved successfully',
            'data' => $detailIncomes
        ]);

    }
}
