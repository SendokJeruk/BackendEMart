<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SellerBalance;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index()
    {
        // ngambil data saldo (balance) seller yang lagi login, sekalian total pemasukannya

        $user = auth()->user();

        $balance = SellerBalance::where('user_id', $user->id)->first();

        return response()->json([
            'status' => 'Success',
            'message' => 'Data Balance retrieved successful',
            'data' => [
                'balance' => $balance,
                'total_income' => $balance ? $balance->jumlah_total : 0,
            ]
        ]);
    }
}
