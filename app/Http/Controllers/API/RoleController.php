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
        try {
            $roles = Role::all();
            return response()->json([
                'message' => 'Berhasil Menampilkan Role',
                'data' => $roles
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
            $validate = Validator::make($request->all(), [
                'nama_role' => 'required',
            ]);

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
                'message' => 'Berhasil Menambahkan Role',
                'data' => $role
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
