<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Income;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(){
        try {
            $user = Auth::user();

            $pendingIncomes = Income::where('user_id', $user->id)
                                    ->where('status', 'pending')
                                    ->get();

            $totalPending = $pendingIncomes->sum('jumlah_total');

            return response()->json([
                'message' => 'Berhasil Dapatkan Data Income',
                'incomes' => $pendingIncomes,
                'total_pending' => $totalPending,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }

        //? Ini buat bikin logic ambil seluruh income user yang login dan
        //? Filter yang status nya pending
        //? terus bikin variable yang isinya income tadi di kalkulasikan semua buat di return
    }

    public function store(){
        //
    }
}
