<?php

namespace App\Http\Middleware\API;

use Closure;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class Seller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $token = PersonalAccessToken::findToken(Str::after(request()->header('Authorization'), "Bearer "));
        // $user = User::findOrFail($token->tokenable_id);
        // $role = $user->role->nama_role;

        $role = auth()->user()->role->nama_role;

        if ($role != 'seller') {
            return response()->json(['message' => 'Halaman tidak ditemukan'], 404);
        }

        return $next($request);
    }
}
