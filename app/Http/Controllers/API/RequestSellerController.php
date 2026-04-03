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
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\RequestSeller\StoreRequest;
use App\Http\Requests\RequestSeller\UpdateRequest;

class RequestSellerController extends Controller
{
    protected $ktp;

    public function __construct() {
        $this->ktp = new UploadKtpRepository();
    }

    public function index()
    {
        // nampilin request seller, decrypt info KTP buat admin atau nampilin request sendiri buat user
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
            'status' => 'Success',
            'message' => 'Request retrieved successfully',
            'data' => $requestSeller
        ]);
    }

    public function store(StoreRequest $request)
    {
        // ngecek request double, enkripsi KTP, trus simpen permohonan jadi seller
        $existing = RequestSeller::where('user_id', Auth::id())->where('status', 'pending')->first();
        if ($existing) {
            return response()->json([
                'message' => 'Kamu sudah pernah mengirim permohonan, mohon bersabar.'
            ], 409);
        }

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
            'status' => 'Success',
            'message' => 'Request submitted successfully',
            'data' => $requestSeller
        ], 201);
    }

    public function update(UpdateRequest $request, RequestSeller $requestSeller)
    {
        // admin update status request. kalo diterima jadi seller, file KTP dihapus demi privasi
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

        if ($requestSeller->foto_ktp) {
            try {
                $fotoPath = Crypt::decryptString($requestSeller->foto_ktp);
                $this->ktp->delete($fotoPath);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Gagal Menghapus Foto KTP',
                ], 500);
            }

            $requestSeller->update([
                'foto_ktp' => null,
            ]);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Request updated successfully',
            'data' => $requestSeller
        ]);
    }
}
