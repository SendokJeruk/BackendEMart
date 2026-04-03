<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Withdraw;
use App\Http\Requests\Withdraw\SubmitWithdrawRequest;
use App\Http\Requests\Withdraw\HandleWithdrawalRequest;

class WithdrawController extends Controller
{
    public function index()
    {
        // nampilin daftar pengajuan penarikan dana dari user buat admin
        $withdraws = Withdraw::with('user:id,name,email')->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Withdraw requests retrieved successfully',
            'data' => $withdraws
        ]);
    }

    public function selfWithdraw()
    {
        // ngambil riwayat penarikan dana punya user yang lagi login aja
        $user = auth()->user();
        $withdraws = Withdraw::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'Success',
            'message' => 'Your withdraw requests retrieved successfully',
            'data' => $withdraws
        ]);
    }

    public function submitWithdraw(SubmitWithdrawRequest $request)
    {
        // ngecek saldo cukup dan nggak ngelanggar limit, trus bikin request withdraw
        $user = auth()->user();
        $id = $user->id;

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

    public function handleWithdrawal(Withdraw $withdraw, HandleWithdrawalRequest $request)
    {
        // proses pengajuan ditarik atau ditolak admin, potong saldo kalo ACC
        if ($withdraw->status !== 'pending') {
            return response()->json([
                'message' => 'This withdraw request has already been processed',
            ], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($withdraw, $request) {
            $withdraw->status = $request->input('status');
            $withdraw->save();

            if ($request->input('status') === 'accepted') {
                $user = $withdraw->user;
                $userBalance = $user->balance;

                if (!$userBalance || $userBalance->balance < $withdraw->jumlah) {
                    throw new \Exception('Insufficient balance or balance record not found');
                }

                $userBalance->balance -= $withdraw->jumlah;
                $userBalance->withdrawn_balance += $withdraw->jumlah;
                $userBalance->save();
            }

            return response()->json([
                'status' => 'Success',
                'message' => 'Withdraw request has been ' . $request->input('status'),
            ]);
        });
    }
}
