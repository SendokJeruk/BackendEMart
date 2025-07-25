<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\RequestSeller;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestSellerController extends Controller
{
    public function index()
    {
        try {
        $user = Auth::user();

        if ($user->role && $user->role->nama_role === 'admin') {
            $requestSeller = RequestSeller::all();
        } else {
            $requestSeller = RequestSeller::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        }

        return response()->json([
            'message' => 'Berhasil Menampilkan Request',
            'data' => $requestSeller
        ]);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    public function store(Request $request)
    {
        try {
            $existing = RequestSeller::where('user_id', Auth::id())->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Kamu sudah pernah mengirim permohonan.'
                ], 409);
            }

            $validate = Validator::make($request->all(), [
                'note' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $requestSeller = new RequestSeller();
            $requestSeller->user_id = auth()->id();
            $requestSeller->note = $request->note;
            $requestSeller->status = 'pending';
            $requestSeller->save();

            return response()->json([
                'message' => 'Permohonan Berhasil Dikirim',
                'data' => $requestSeller
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, RequestSeller $requestSeller)
    {
    try {
        $validate = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected',
            'note' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $requestSeller->update([
            'status' => $request->status,
            'note' => $request->note,
        ]);

        if ($request->status === 'accepted') {
            $user = User::find($requestSeller->user_id);
            $role = Role::where('nama_role', 'seller')->first();
            $user->role_id = $role->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Permohonan Berhasil Diubah',
            'data' => $requestSeller
        ]);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
