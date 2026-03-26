<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
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
        // $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->access_token = $token;
        $user->token_type = 'Bearer';

        return response()->json([
            'status' => 'Success',
            'message' => 'Login successful',
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

        $data = $request->only([
            'name',
            'email',
            'no_telp',
        ]);
        $data['password'] = Hash::make($request->password);
        $data['role_id'] = $id;

        $token = Str::random(67);

        Cache::put('pending_user_' . $token, $data, 3600); // 60 minutes

        $url = route('verify', ['token' => $token]);

        // return response()->json([
        //     $data,
        //     $url
        // ]);

        try {
            Log::info('Queueing email via driver: ' . config('mail.default'));
            Mail::to($data['email'])->queue(new VerifyEmail($url, $data['name']));
            Log::info('Email successfully queued for ' . $data['email']);
        } catch (Exception $e) {
            Log::error('GAGAL KIRIM EMAIL');
            return response()->json([
                'status' => 'Error',
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Registration successful, check your email to verify',
            'data' => $data
        ], 201);
    }

    public function logout()
    {

        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Logout successful',
        ]);
    }

    public function verify($token)
    {
        $data = Cache::get('pending_user_' . $token);

        if (!$data) {
            return response()->json(['message' => 'Token expired'], 400);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_telp' => $data['no_telp'],
            'role_id' => $data['role_id'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        Cache::forget('pending_user_' . $token);

        return response()->json([
            'status' => 'Success',
            'message' => 'Verify successful',
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
