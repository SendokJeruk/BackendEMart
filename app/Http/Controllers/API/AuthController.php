<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use HasApiTokens, Notifiable;
    public function login(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
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

    }

    public function register(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'no_telp' => 'required',
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
            ],
            'role_id' => 'nullable',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid Data',
                'errors' => $validate->errors()
            ], 422);
        }

        $findUserRole = Role::where('nama_role', 'buyer')->first();
        if (!$findUserRole) {
            $addUserRole = new Role();
            $addUserRole->nama_role = 'buyer';
            $addUserRole->save();

            $id = $addUserRole->id;
        } else {
            $id = $findUserRole->id;
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->no_telp = $request->input('no_telp');
        $user->role_id = $id;
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'Registrasi Berhasil',
            'data' => $user
        ]);

    }

    public function logout()
    {

        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);

    }

    // GOOGLE AUTH
    // public function redirect()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

    // public function callback()
    // {
    //
    //         $socialUser = Socialite::driver('google')->user();
    //         $registeredUser = User::where("google_id", $socialUser->id)->first();
    //         $roleId = Role::where('nama_role', 'user')->value('id');

    //         if (!$registeredUser) {
    //             $user = User::updateOrCreate(
    //                 ['google_id' => $socialUser->id],
    //                 [
    //                     'name' => $socialUser->name,
    //                     'email' => $socialUser->email,
    //                     'no_telp' => '000000000000',
    //                     'password' => Hash::make(Str::random(16)),
    //                     'role_id' => $roleId,
    //                     'google_token' => $socialUser->token,
    //                     'google_refresh_token' => $socialUser->refreshToken,
    //                 ]
    //             );

    //             Auth::login($user);
    //         } else {
    //             Auth::login($registeredUser);
    //         }

    //         // Buat token API untuk user
    //         $token = Auth::user()->createToken('auth_token')->plainTextToken;

    //         return response()->json([
    //             'token' => $token,
    //             'user' => Auth::user(),
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'error' => 'Google login failed!',
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString() // Tambahkan trace untuk melihat detail error
    //         ], 500);
    //     }
    // }
    // GOOGLE AUTH
}
