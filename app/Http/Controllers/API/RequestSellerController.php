<?php

namespace App\Http\Controllers\API;

use App\Repository\UploadKtpRepository;
use Exception;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\RequestSeller;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;


class RequestSellerController extends Controller
{
    protected $ktp;

    public function __construct() {
        $this->ktp = new UploadKtpRepository();
    }
    public function index()
    {
        try {
            $user = Auth::user();

            if ($user->role && $user->role->nama_role === 'admin') {
                $requestSeller = RequestSeller::all();

            foreach ($requestSeller as $seller) {
                $seller->nik = Crypt::decryptString($seller->nik);
                $seller->alamat_ktp = Crypt::decryptString($seller->alamat_ktp);
                $seller->foto_ktp = Crypt::decryptString($seller->foto_ktp);
            }
            } else {
                $requestSeller = RequestSeller::with('user')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();

             if ($requestSeller) {
                $requestSeller->nik = Crypt::decryptString($requestSeller->nik);
                $requestSeller->alamat_ktp = Crypt::decryptString($requestSeller->alamat_ktp);
                $requestSeller->foto_ktp = Crypt::decryptString($requestSeller->foto_ktp);
            }
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
            $existing = RequestSeller::where('user_id', Auth::id())->where('status', 'pending')->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Kamu sudah pernah mengirim permohonan, mohon bersabar.'
                ], 409);
            }

            $validate = Validator::make($request->all(), [
                'note' => 'required|string|max:100',
                'nik' => 'required|digits:16|unique:request_sellers,nik',
                'nama_lengkap' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                    'tanggal_lahir' => 'required|date|before:today',
                'jenis_kelamin' => 'required|in:L,P',
                'alamat_ktp' => 'required|string|max:500',
                'foto_ktp' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

        // $filePath = $this->ktp->save($request->file('foto_ktp'));
        // if (!$filePath) {
        //     throw new \Exception('Gagal mengunggah foto KTP.');
        // }

            $requestSeller = new RequestSeller();
            $requestSeller->user_id = auth()->id();
            $requestSeller->note = $request->note;
            $requestSeller->nik = Crypt::encryptString($request->nik);
            $requestSeller->nama_lengkap = $request->nama_lengkap;
            $requestSeller->tempat_lahir = $request->tempat_lahir;
            $requestSeller->tanggal_lahir = $request->tanggal_lahir;
            $requestSeller->jenis_kelamin = $request->jenis_kelamin;
            $requestSeller->alamat_ktp = Crypt::encryptString($request->alamat_ktp);
            $requestSeller->foto_ktp = Crypt::encryptString($this->ktp->save($request->file('foto_ktp')));
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
