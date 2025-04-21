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
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'no_telp' => $user->no_telp,
            'foto_profil' => $user->foto_profil,
            'nama_role' => $user->role->nama_role ?? null,
        ];

        return response()->json([
            'message' => "Berhasil Mendapatkan Data Profil",
            'data' => $data
        ]);
    }

    public function update(Request $request)
    {
        $updateUser = auth()->user();

        $validate = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_telp' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'role_id' => 'nullable|integer',
            'foto_profil' => 'nullable|file|image|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = [];

        $fields = ['name', 'email', 'no_telp', 'password', 'role_id'];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                if ($field === 'password') {
                    $data[$field] = Hash::make($request->password);
                } else {
                    $data[$field] = $request->$field;
                }
            }
        }

        $updateUser->update($data);

        if ($request->hasFile('foto_profil')) {
            $newImage = $request->file('foto_profil');

            if ($updateUser->foto_profil) {
                $updateUser->foto_profil = $this->upload->update($updateUser->foto_profil, $newImage);
            } else {
                $updateUser->foto_profil = $this->upload->save($newImage);
            }

            $updateUser->save();
        }

        return response()->json([
            'message' => 'Profil telah diperbarui',
            'data' => $updateUser
        ], 200);
    }
}
