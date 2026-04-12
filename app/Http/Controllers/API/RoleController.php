<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::all();
        return response()->json([
            'status' => 'Success',
            'message' => 'Role retrieved successfully',
            'data' => $roles
        ]);
    }

    public function store(StoreRequest $request)
    {
        // bikin role baru, tapi dicek dulu biar namanya nggak duplikat
        $existingRole = Role::where('nama_role', $request->nama_role)->first();
        if ($existingRole) {
            return response()->json([
                'status' => false,
                'message' => 'Role sudah ada, tidak boleh duplikat.'
            ], 409);
        }

        $role = new Role();
        $role->nama_role = $request->nama_role;
        $role->save();

        return response()->json([
            'status' => 'Success',
            'message' => 'Role added successfully',
            'data' => $role
        ], 201);
    }

    public function update(UpdateRequest $request, Role $role)
    {
        // ngupdate nama role yang udah ada
        $role->update([
            'nama_role' => $request->nama_role
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Role updated successfully',
            'data' => $role
        ], 200);
    }

    public function delete(Role $role)
    {
        // ngapus role dari sistem
        $role->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Data deleted successfully'
        ]);
    }
}
