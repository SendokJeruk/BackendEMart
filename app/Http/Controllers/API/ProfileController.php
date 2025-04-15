<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Repository\UploadProfileRepository;

class ProfileController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadProfileRepository();
    }
    public function index()
    {
        $user = auth()->user()->load('role'); 

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'no_telp' => $user->no_telp,
            'foto_profil' => $user->foto_profil,
            'nama_role' => $user->role->nama_role ?? null,
        ];
        // $data = auth()->user()->only(['name', 'email', 'no_telp', 'foto_profil']);
        return response()->json([
            'message' => "Berhasil Mendapatkan Data Profil",
            'data' => $data
        ]);
    }

    // public function update(Request $request){
    //     $updateUser = auth()->user();
    //     $validate = Validator::make($request->all(),[
    //         'name' => 'required',
    //         'email' => 'required',
    //         'no_telp' => 'required',
    //         'password' => 'required',
    //         'role_id' => 'required',
    //         'foto_profil' => 'nullable',
    //     ]);

    //     if($validate->fails()) {
    //         return response()->json([
    //             'message' => 'Invalid Data',
    //             'errors' => $validate->errors()
    //         ], 422);
    //     }

    //     $updateUser->update([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'no_telp' => $request->no_telp,
    //         'password' => Hash::make($request->password),
    //         'role_id' => $request->role_id,
    //         'foto_profil' => $request->foto_profil,
    //     ]);

    //     return response()->json([
    //         'message' => 'Profil telah di perbarui',
    //         'data' => $updateUser
    //         ], 200);
    // }
}
