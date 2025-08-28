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
        $user = auth()->user();

        $detailIncomes = DetailIncome::with(['income', 'detailTransaksi.product'])
            ->whereHas('income', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Berhasil mendapatkan data income',
            'data' => $detailIncomes
        ]);

    }
}
