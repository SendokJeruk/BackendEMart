<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Repository\UploadProfileRepository;
use App\Http\Requests\ManageUser\StoreRequest;
use App\Http\Requests\ManageUser\UpdateRequest;

class ManageUserController extends Controller
{
    protected $upload;

    public function __construct()
    {
        // ngejalanin fungsi __construct
        $this->upload = new UploadProfileRepository();
    }

    public function index(Request $request)
    {
        // ngambil data semua user, bisa difilter by nama atau ID, biasanya buat admin
        $query = User::query();
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        } elseif ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $manage_user = $query->paginate(10);
        return response()->json([
            'status' => 'Success',
            'message' => 'Data retrieved successfully',
            'data' => $manage_user
        ]);
    }

    public function store(StoreRequest $request)
    {
        // bikin user baru (biasanya lewat admin), trus ngupload foto profilnya sekalian
        $manage_user = new User();
        $manage_user->name = $request->input('name');
        $manage_user->email = $request->input('email');
        $manage_user->no_telp = $request->input('no_telp');
        $manage_user->role_id = $request->input('role_id');
        $manage_user->password = Hash::make($request->input('password'));
        
        if ($request->hasFile('foto_profil')) {
            $manage_user->foto_profil = $this->upload->save($request->file('foto_profil'));
        } else {
            $manage_user->foto_profil = null;
        }
        $manage_user->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Successful added data',
            'data' => $manage_user
        ], 201);
    }

    public function update(UpdateRequest $request, User $manage_user)
    {
        // admin ngedit data user, termasuk ganti password sama update foto profil kalo ada
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
        return response()->json([
            'status' => 'Success',
            'message' => 'Data updated successfully',
            'data' => $manage_user
        ], 200);
    }

    public function delete(User $manage_user)
    {
        // ngapus data user dari sistem
        $manage_user->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
