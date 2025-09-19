<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request)
    {

            $validate = Validator::make($request->all(), [
                'nama_role' => 'required|string|max:100',
            ]);

            $existingRole = Role::where('nama_role', $request->nama_role)->first();
            if ($existingRole) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role sudah ada, tidak boleh duplikat.'
                ], 409);
            }
            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $role = new Role();
            $role->nama_role = $request->nama_role;
            $role->save();

            return response()->json([
                'status' => 'Success',
                'message' => 'Role added successfully',
                'data' => $role
            ],201);

    }

    public function update( Request $request, Role $role){

            $validate = Validator::make($request->all(),[
                'nama_role' => 'nullable',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $role->update([
                'nama_role' => $request->nama_role
            ]);

            return response()->json([
                'status' => 'Success',
                'message' => 'Role updated successfully',
                'data' => $role
                ], 200);

    }

    public function delete(Role $role){

            $role->delete();

            return response()->json([
                'status' => 'Success',
                'message' => 'Data deleted successfully'
         ]);

    }
}
