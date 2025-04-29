<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Welcome to Eleven Market API',
        'meta' => [
            'timestamp' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'app_env' => App::environment(),
            'app_version' => '0.1.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ],
        'data' => [
            'project' => [
                'name' => 'Eleven Market',
                'description' => 'A modern API for Eleven Market platform.',
                'status' => 'active',
            ],
            'developer' => [
                'group' => 'Sendok Jeruk Teams'
            ],
            'documentation' => 'https://github.com/SendokJeruk/BackendEMart',
        ],
    ], 200, [
        'Content-Type' => 'application/json',
    ]);
});

// Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
//     Route::post('login', [AuthController::class, 'login']);
//     Route::post('register', [AuthController::class, 'register']);
//     // GOOGLE OAUTH
//     Route::get('google/redirect', [AuthController::class, 'redirect'])->name('redirect');
//     Route::get('google/callback', [AuthController::class, 'callback'])->name('callback');
//     // GOOGLE OAUTH
// });
