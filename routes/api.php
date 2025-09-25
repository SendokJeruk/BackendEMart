<?php

use App\Models\AlamatUser;
use App\Models\DetailIncome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\FotoController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\TokoController;
use App\Http\Controllers\API\AlamatController;
use App\Http\Controllers\API\IncomeController;
use App\Http\Controllers\API\MidtransCallback;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\EncryptController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\ShipmentController;
use App\Http\Controllers\API\DetailCartController;
use App\Http\Controllers\API\ManageUserController;
use App\Http\Controllers\API\PengirimanCOntroller;
use App\Http\Controllers\API\RajaOngkirController;
use App\Http\Controllers\API\FotoProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\DetailIncomeController;
use App\Http\Controllers\API\RequestSellerController;
use App\Http\Controllers\API\CategoryProductController;
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

Route::get('/test-limit', function () {
    return response()->json(['ok' => true]);
})->middleware('throttle:test');


Route::post('midtrans/callback', [MidtransCallback::class, 'callback']);

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // GOOGLE OAUTH
    // Route::group(['middleware' => ['web']], function () {
    //     // your routes here
    //     Route::get('google/redirect', [AuthController::class, 'redirect'])->name('redirect');
    //     Route::get('google/callback', [AuthController::class, 'callback'])->name('callback');
    // });
    // GOOGLE OAUTH
});


Route::get('/product/mobile', [ProductController::class, 'index'])->middleware('auth:sanctum');
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
    Route::get('/get-all-transaction', [TransactionController::class, 'getAllTransaction'])->middleware('checkrole');
    Route::get('/get-transaction-detail/{transaction}', [TransactionController::class, 'getTransactionDetail']);

    Route::post('/', [TransactionController::class, 'store']);
    Route::put('/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/{transaction}', [TransactionController::class, 'delete']);

    Route::get('/pesanan-masuk', [TransactionController::class, 'pesananMasuk']);

    // MIDTRANS CUY
    Route::post('/payment/{transaction}', [TransactionController::class, 'createTransaction']);
});
Route::get('/transaction', [TransactionController::class, 'index'])->middleware('auth:sanctum');
Route::post('/test',[TestController::class, 'test']);
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
    Route::post('/', [TokoController::class, 'store']);
    Route::put('/alamat/{toko}', [TokoController::class, 'updateAlamat']);
    Route::put('/{toko}', [TokoController::class, 'update']);
    Route::delete('/{toko}', [TokoController::class, 'delete']);
});
Route::get('/toko', [TokoController::class, 'index'])->middleware('auth:sanctum');

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

Route::group(['prefix' => 'alamat', 'as' => 'alamat.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [AlamatController::class, 'get']);
    Route::post('/', [AlamatController::class, 'store']);
    Route::put('/{alamat}', [AlamatController::class, 'update']);
    Route::delete('/{alamat}', [AlamatController::class, 'delete']);
});

Route::group(['prefix' => 'setting', 'as' => 'setting.', 'middleware' => ['auth:sanctum', 'checkrole']], function () {
    Route::get('/', [SettingController::class, 'index']);
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
    Route::post('/checkoutAll', [CheckoutController::class, 'checkoutAll']);
    Route::post('/products', [CheckoutController::class, 'checkout']);
});

Route::group(['prefix' => 'income', 'as' => 'income.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [IncomeController::class, 'index']);
});

Route::group(['prefix' => 'requestseller', 'as' => 'requestseller.', 'middleware' => ['auth:sanctum', ]], function () {
    Route::get('/', [RequestSellerController::class, 'index']);
    Route::post('/', [RequestSellerController::class, 'store']);
});
Route::put('/requestseller/{requestSeller}', [RequestSellerController::class, 'update'])->middleware(['auth:sanctum', 'checkrole']);
Route::group(['prefix' => 'detailIncome', 'as' => 'detailIncome.', 'middleware' => ['auth:sanctum', ]], function () {
    Route::get('/', [DetailIncomeController::class, 'index']);
});

Route::group(['prefix' => 'pengiriman', 'as' => 'pengiriman.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [ShipmentController::class, 'getAllPengiriman']);
    Route::get('/{kode_transaksi}', [ShipmentController::class, 'getPengirimanByKodeTransaksi']);
    Route::post('/', [ShipmentController::class, 'store']);
    Route::post('/confirm-received/{pengiriman}', [ShipmentController::class, 'confirmReceived']);
    Route::put('/{pengiriman}', [ShipmentController::class, 'update']);
    Route::delete('/{pengiriman}', [ShipmentController::class, 'delete']);
});

Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['auth:sanctum', 'checkrole']], function () {
   Route::get('/admin', [ReportController::class, 'adminMonthlyReport'])->name('admin');
});
Route::get('report/seller/{seller_id}', [ReportController::class, 'sellerTransactionReport'])->middleware(['auth:sanctum', 'seller']);
Route::get('report/user/{user_id}', [ReportController::class, 'userTransactionReport'])->middleware('auth:sanctum');

// TES ENKRIPSI
Route::post('/enkrypt', [EncryptController::class, 'enkrypt']);
Route::post('/decrypt', [EncryptController::class, 'decrypt']);

Route::post('/testincome', [TransactionController::class, 'test']);
