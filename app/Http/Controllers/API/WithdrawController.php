<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Withdraw;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends Controller
{
    public function index()
    {
        $withdraws = Withdraw::with('user:id,name,email')->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Withdraw requests retrieved successfully',
            'data' => $withdraws
        ]);
    }

    public function selfWithdraw()
    {
        $user = auth()->user();
        $withdraws = Withdraw::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Your withdraw requests retrieved successfully',
            'data' => $withdraws
        ]);
    }

    public function submitWithdraw(Request $request)
    {
        $user = auth()->user();
        $id = $user->id;
        $validate = Validator::make($request->all(), [
            'jumlah' => 'required|numeric|min:10000',
            'metode' => 'required|string|in:bank_transfer,gopay,ovo,dana,shopeePay',
            'rekening_tujuan' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:255',
        ]);

        $pending = $user->withdraw()->where('status', 'pending')->first();
        if ($pending) {
            return response()->json([
                'message' => 'You already have a pending withdraw request. Please wait for it to be processed.'
            ], 422);
        }

        $userIncome = $user->income->jumlah_total ?? 0;

        if ($request->input('jumlah') > $userIncome) {
            return response()->json([
                'message' => 'Insufficient funds for this withdraw request',
            ], 422);
        }

        if ($request->input('jumlah') > 1000000) {
            return response()->json([
                'message' => 'Withdraw amount exceeds the limit',
            ], 422);
        }

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $withdraw = new Withdraw();
        $withdraw->user_id = $id;
        $withdraw->jumlah = $request->input('jumlah');
        $withdraw->metode = $request->input('metode');
        $withdraw->rekening_tujuan = $request->input('rekening_tujuan');
        $withdraw->catatan = $request->input('catatan');
        $withdraw->status = 'pending';
        $withdraw->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Withdraw request submitted successfully',
        ]);
    }

    public function handleWithdrawal(Withdraw $withdraw, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        if ($withdraw->status !== 'pending') {
            return response()->json([
                'message' => 'This withdraw request has already been processed',
            ], 422);
        }

        $withdraw->status = $request->input('status');
        $withdraw->save();

        if ($request->input('status') === 'accepted') {
            $user = $withdraw->user;
            $userIncome = $user->income;

            if ($userIncome) {
                $userIncome->jumlah_total -= $withdraw->jumlah;
                $userIncome->save();
            }
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Withdraw request has been ' . $request->input('status'),
        ]);
    }
}
