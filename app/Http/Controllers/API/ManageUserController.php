<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Repository\UploadProfileRepository;


class ManageUserController extends Controller
{
    protected $upload;

    public function __construct()
    {
        $this->upload = new UploadProfileRepository();
    }
    public function index(Request $request){
        try {
            $query = User::query();

            if ($request->has('name')) {
                $query->where('name', 'like', "%{$request->name}%");
            }

            elseif ($request->has('id')) {
                $query->where('id', $request->id);
            }

            $manage_user = $query->paginate(10);
            return response()->json([
                'message' => 'Berhasil Dapatkan Data',
                'data' => $manage_user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        try {
            $validate = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required',
                'no_telp' => 'required',
                'password' => 'required|min:8',
                'role_id' => 'required',
                'foto_profil' => 'nullable|image',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $manage_user = new User();
            $manage_user->name = $request->input('name');
            $manage_user->email = $request->input('email');
            $manage_user->no_telp = $request->input('no_telp');
            $manage_user->role_id = $request->input('role_id');
            $manage_user->password = Hash::make($request->input('password'));
            $manage_user->foto_profil = $this->upload->save($request->file('foto_profil'));
            $manage_user->save();

            return response()->json([
                'message' => 'data telah di tambahkan',
                'data' => $manage_user
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, User $manage_user){
        $validate = Validator::make($request->all(),[
            'name' => 'nullable',
            'email' => 'nullable',
            'no_telp' => 'nullable',
            'password' => 'nullable',
            'role_id' => 'nullable',
            'foto_profil' => 'nullable',
        ]);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $manage_user->update([
            'name' => $request->name,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        if ($request->hasFile('foto_profil')) {
            $newImage = $request->file('foto_profil');

            $manage_user->foto_profil = $this->upload->update($manage_user->foto_profil, $newImage);

            $manage_user->save();
        }
        $manage_user->save();
        return response()->json([
            'message' => 'data telah di perbarui',
            'data' => $manage_user
            ], 200);
    }

    public function delete(User $manage_user){
        try {
            $manage_user->delete();

            return response()->json([
             'message' => 'Data berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
