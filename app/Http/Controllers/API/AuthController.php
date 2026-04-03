<?php
namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Http\Requests\Auth\SendResetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

class AuthController extends Controller
{
    use HasApiTokens, Notifiable;

    public function login(LoginRequest $request)
    {
        // ngecek email sama password, kalo cocok bakal dibuatin token akses buat login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->access_token = $token;
        $user->token_type = 'Bearer';

        return response()->json([
            'status' => 'Success',
            'message' => 'Login successful',
            'data' => $user
        ]);
    }

    public function register(RegisterRequest $request)
    {
        // bikin akun baru dengan role buyer secara default, trus ngirim email verifikasi
        $findUserRole = Role::where('nama_role', 'buyer')->first();
        if (!$findUserRole) {
            $addUserRole = new Role();
            $addUserRole->nama_role = 'buyer';
            $addUserRole->save();
            $id = $addUserRole->id;
        } else {
            $id = $findUserRole->id;
        }

        $data = $request->only(['name', 'email', 'no_telp']);
        $data['password'] = Hash::make($request->password);
        $data['role_id'] = $id;

        $token = Str::random(67);
        $temp_data = Cache::put('pending_user_' . $token, $data, 3600);
        $url = env('FRONTEND_URL', 'http://localhost:5173/') . 'verify?token=' . $token;

        try {
            Log::info('VERIFY DATA URL : ' . $url);
            Log::info('Queueing email via driver: ' . config('mail.default'));
            Mail::to($data['email'])->send(new VerifyEmail($url, $data['name']));
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
        // ngapus token akses user yang lagi dipake biar bener-bener keluar dari sistem
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Logout successful',
        ]);
    }

    public function verify(VerifyRequest $request)
    {
        // ngecek token dari email, kalo bener datanya dipindah dari cache ke tabel user asli
        $token = $request->input('token');
        $data = Cache::get('pending_user_' . $token);

        if (!$data) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Data mismatch/expired'
            ], 400);
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

    public function sendResetPassword(SendResetPasswordRequest $request)
    {
        // nyari user dari email, trus bikin token n ngirim link reset password ke emailnya
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(67);
            $email = $user->email;
            Cache::put('pending_resetpass_' . $token, $email, now()->addMinutes(15));
            $url = env('FRONTEND_URL', 'http://localhost:5173/') . 'reset-password?token=' . $token . '&email=' . $email;

            Log::info('RESET PASS URL : ' . $url);
            Mail::to($email)->send(new ResetPasswordEmail($url, $user->name));

            return response()->json([
                'status' => 'Success',
                'message' => 'Tautan reset password telah dikirim ke email Anda.',
            ]);
        }

        return response()->json([
            'status' => 'Failed',
            'message' => 'User tidak ditemukan.'
        ], 404);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        // validasi token reset, kalo valid langsung ganti password user di database
        $token = $request->input('token');
        $email = $request->input('email');

        $cachedEmail = Cache::get('pending_resetpass_' . $token);

        if (!$cachedEmail || $cachedEmail !== $email) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Tautan tidak valid atau sudah kedaluwarsa.'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'Failed',
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget('pending_resetpass_' . $token);

        return response()->json([
            'status' => 'Success',
            'message' => 'Password berhasil diubah!'
        ]);
    }
}
