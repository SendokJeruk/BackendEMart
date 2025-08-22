<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Income;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index()
    {

        $user = auth()->user();

        $income = Income::where('user_id', $user->id)->first();

        return response()->json([
            'message' => 'Berhasil mendapatkan data income',
            'data' => [
                'income' => $income,
                'total_income' => $income ? $income->jumlah_total : 0,
            ]
        ]);
    }

}
