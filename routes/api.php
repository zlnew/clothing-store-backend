<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('vouchers', VoucherController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/authorize', fn () => auth()->check());

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('vouchers', VoucherController::class)->except(['index', 'show']);
    Route::apiResource('transactions', TransactionController::class);
});

require __DIR__.'/auth.php';
