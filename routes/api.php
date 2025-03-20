<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\DetailTransactionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    // GOOGLE OAUTH
    // Route::group(['middleware' => ['web']], function () {
    //     // your routes here
    //     Route::get('google/redirect', [AuthController::class, 'redirect'])->name('redirect');
    //     Route::get('google/callback', [AuthController::class, 'callback'])->name('callback');
    // });
    // GOOGLE OAUTH
});


Route::group(['prefix' => 'product', 'as' => 'product.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/draft', [ProductController::class, 'draft']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{product}', [ProductController::class, 'edit']);
    Route::delete('/{product}', [ProductController::class, 'delete']);
});

Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'delete']);
});

Route::group(['prefix' => 'rating', 'as' => 'rating.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [RatingController::class, 'index']);
    Route::post('/', [RatingController::class, 'store']);
});

Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    // Route::put('/{category}', [CategoryController::class, 'update']);
    // Route::delete('/{category}', [CategoryController::class, 'delete']);
});

Route::group(['prefix' => 'transaction', 'as' => 'transaction.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::put('/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/{transaction}', [TransactionController::class, 'delete']);
});

Route::group(['prefix' => 'detail-transaction', 'as' => 'detail-transaction.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [DetailTransactionController::class, 'index']);
    Route::post('/', [DetailTransactionController::class, 'store']);
    Route::put('/{detailTransaction}', [DetailTransactionController::class, 'update']);
    Route::delete('/{detailTransaction}', [DetailTransactionController::class, 'delete']);
});
