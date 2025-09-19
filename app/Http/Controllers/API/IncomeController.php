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
            'status' => 'Success',
            'message' => 'Data Income retrieved successful',
            'data' => [
                'income' => $income,
                'total_income' => $income ? $income->jumlah_total : 0,
            ]
        ]);
    }

}
