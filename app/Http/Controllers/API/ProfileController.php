<?php
namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Repository\UploadProfileRepository;
use App\Http\Requests\Profile\UpdateRequest;

class ProfileController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadProfileRepository();
    }

    public function index()
    {
        // ngambil data profil user yang lagi login beserta rolenya
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
            'status' => 'Success',
            'message' => "Data Profile retrieved successfully",
            'data' => $data
        ]);
    }

    public function update(UpdateRequest $request)
    {
        // ngupdate data profil user, encrypt password kalo diganti, dan ngurus upload foto profil baru
        $updateUser = auth()->user();
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
            'status' => 'Success',
            'message' => 'Profile updated successfully',
            'data' => $updateUser
        ], 200);
    }
}
