<?php

use App\Models\AlamatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\FotoController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\TokoController;
use App\Http\Controllers\API\AlamatController;
use App\Http\Controllers\API\MidtransCallback;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DetailCartController;
use App\Http\Controllers\API\ManageUserController;
use App\Http\Controllers\API\RajaOngkirController;
use App\Http\Controllers\API\FotoProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\CategoryProductController;
use App\Http\Controllers\API\CheckoutController;
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

Route::post('midtrans/callback', [MidtransCallback::class, 'callback']);

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // GOOGLE OAUTH
    // Route::group(['middleware' => ['web']], function () {
    //     // your routes here
    //     Route::get('google/redirect', [AuthController::class, 'redirect'])->name('redirect');
    //     Route::get('google/callback', [AuthController::class, 'callback'])->name('callback');
    // });
    // GOOGLE OAUTH
});


Route::get('/product', [ProductController::class, 'index']);
Route::group(['prefix' => 'product', 'as' => 'product.', 'middleware' => ['auth:sanctum', 'seller'] ], function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{product}', [ProductController::class, 'edit']);
    Route::delete('/{product}', [ProductController::class, 'delete']);
});

Route::get('/category', [CategoryController::class, 'index']);
Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['auth:sanctum', 'checkrole']], function () {
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
    Route::put('/{role}', [RoleController::class, 'update']);
    Route::delete('/{role}', [RoleController::class, 'delete']);
});

Route::group(['prefix' => 'transaction', 'as' => 'transaction.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::put('/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/{transaction}', [TransactionController::class, 'delete']);

    // MIDTRANS CUY
    Route::post('/payment/{transaction}', [TransactionController::class, 'createTransaction']);
});
Route::post('/transaction/payment/callback', [TransactionController::class, 'callback'])->withoutMiddleware('auth:sanctum');

Route::group(['prefix' => 'detail-transaction', 'as' => 'detail-transaction.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [DetailTransactionController::class, 'index']);
    Route::post('/', [DetailTransactionController::class, 'store']);
    Route::put('/{detailTransaction}', [DetailTransactionController::class, 'update']);
    Route::delete('/{detailTransaction}', [DetailTransactionController::class, 'delete']);
});

Route::group(['prefix' => 'category-product', 'as' => 'category-product.', 'middleware' => ['auth:sanctum', 'seller']], function () {
    Route::get('/', [CategoryProductController::class, 'index']);
    Route::post('/', [CategoryProductController::class, 'store']);
    Route::put('/{category_product}', [CategoryProductController::class, 'update']);
    Route::delete('/{category_product}', [CategoryProductController::class, 'delete']);
});

Route::group(['prefix' => 'manage-user', 'as' => 'manage-user.', 'middleware' => ['auth:sanctum', 'checkrole']], function () {
    Route::get('/', [ManageUserController::class, 'index']);
    Route::post('/', [ManageUserController::class, 'store']);
    Route::put('/{manage_user}', [ManageUserController::class, 'update']);
    Route::delete('/{manage_user}', [ManageUserController::class, 'delete']);
});

Route::group(['prefix' => 'toko', 'as' => 'toko.', 'middleware' => ['auth:sanctum', 'seller',]], function () {
    Route::get('/', [TokoController::class, 'index']);
    Route::post('/', [TokoController::class, 'store']);
    Route::put('/{toko}', [TokoController::class, 'update']);
    Route::delete('/{toko}', [TokoController::class, 'delete']);
});

Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::put('/', [ProfileController::class, 'update']);
});

Route::group(['prefix' => 'foto-product', 'as' => 'foto-product.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [FotoProductController::class, 'index']);
    Route::post('/', [FotoProductController::class, 'store']);
    Route::put('/{fotoProduct}', [FotoProductController::class, 'update']);
    Route::delete('/{fotoProduct}', [FotoProductController::class, 'delete']);
});

Route::group(['prefix' => 'foto', 'as' => 'foto.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [FotoController::class, 'index']);
    Route::post('/', [FotoController::class, 'store']);
    Route::put('/{foto}', [FotoController::class, 'update']);
    Route::delete('/{foto}', [FotoController::class, 'delete']);
});

Route::group(['prefix' => 'rajaongkir', 'as' => 'rajaongkir.'], function () {
    Route::get('/domestic', [RajaOngkirController::class, 'domestic']);
    Route::get('/cities', [RajaOngkirController::class, 'cities']);
    Route::post('/cost', [RajaOngkirController::class, 'cost']);
    Route::post('/track', [RajaOngkirController::class, 'track']);
});

Route::group(['prefix' => 'alamat', 'as' => 'alamat.'], function () {
    // Route::get('/domestic', [RajaOngkirController::class, 'domestic']);
    // Route::get('/cities', [RajaOngkirController::class, 'cities']);
    Route::post('/', [AlamatController::class, 'store']);
    // Route::post('/track', [RajaOngkirController::class, 'track']);
});

Route::group(['prefix' => 'setting', 'as' => 'setting.', 'middleware' => ['auth:sanctum', 'checkrole']], function () {
    Route::get('/tes', [SettingController::class, 'test']);
    Route::post('/', [SettingController::class, 'update']);
});

Route::group(['prefix' => 'detailcart', 'as' => 'detailcart.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [DetailCartController::class, 'index']);
    Route::post('/', [DetailCartController::class, 'store']);
    Route::put('/{Cart_detail}', [DetailCartController::class, 'update']);
    Route::delete('/{Cart_detail}', [DetailCartController::class, 'delete']);
});

Route::group(['prefix' => 'cart', 'as' => 'cart.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/{Cart_detail}', [CartController::class, 'update']);
    Route::delete('/{Cart_detail}', [CartController::class, 'delete']);
});

Route::group(['prefix' => 'checkout', 'as' => 'checkout.', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/', [CheckoutController::class, 'checkout']);
});
