<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAdminController;
use App\Http\Controllers\ApiAdminUserController;
use App\Http\Controllers\ApiAdminProductController;

Route::post('admin/login', [ApiAdminController::class, 'apiLogin']);
Route::post('admin/registerUser', [ApiAdminUserController::class, 'apiRegisterUser']);
Route::post('user/login', [ApiAdminUserController::class, 'apiUserLogin']);
Route::post('user/register', [ApiAdminUserController::class, 'apiRegisterUser']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('admin/logout', [ApiAdminController::class, 'apiLogout']);
    Route::get('admin/getListProduk', [ApiAdminProductController::class, 'index']);
    Route::post('admin/addProduk', [ApiAdminProductController::class, 'store']);
    Route::get('admin/getProduk/{id}', [ApiAdminProductController::class, 'show']);
    Route::put('admin/updateProduk/{id}', [ApiAdminProductController::class, 'update']);
    Route::delete('admin/deleteProduk/{id}', [ApiAdminProductController::class, 'destroy']);

    // user api
    Route::post('user/logout', [ApiAdminUserController::class, 'apiLogout']);
    Route::post("user/addToCart/{id}", [ApiAdminUserController::class, 'addToCart']);
    Route::get("user/showCart", [ApiAdminUserController::class, 'showCart']);
    Route::post("user/updateQuantity/{id}", [ApiAdminUserController::class, 'updateQuantity']);
    Route::post("user/deleteItem/{id}", [ApiAdminUserController::class, 'removeFromCart']);
    Route::post("user/checkout", [ApiAdminUserController::class, 'checkout']);
    Route::post("user/hapusCart", [ApiAdminUserController::class, 'clearCart']);
});
