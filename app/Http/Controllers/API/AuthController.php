<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) {
        try {
            $validate = Validator::make($request->all(),[
                'email' => 'required',
                'password' => 'required'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->access_token = $token;
            $user->token_type = 'Bearer';

            return response()->json([
                'message' => 'Login Berhasil',
                'data' => $user
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request) {
        try {
            $validate = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required',
                'no_telp' => 'required',
                'password' => 'required',
                'role_id' => 'required',
            ]);

            if($validate->fails()) {
                return response()->json([
                    'message' => 'Invalid Data',
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->no_telp = $request->input('no_telp');
            $user->role_id = $request->input('role_id');
            $user->password = Hash::make($request->input('password'));
            $user->save();

            return response()->json([
                'message' => 'Registrasi Berhasil',
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
