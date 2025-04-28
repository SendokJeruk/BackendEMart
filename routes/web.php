<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

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

Route::get('/', function () {
    return response()->json([
        'title' => 'Eleven Market',
        'api_version' => '0.1',
        'status' => 'active',
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
