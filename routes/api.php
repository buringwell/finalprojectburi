<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PelangganDataController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\AlatController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\PenyewaanDetailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route yang tidak memerlukan autentikasi (contoh: login, register)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/passnew',[AuthController::class,'update_pass']);
});


// Route yang memerlukan autentikasi
Route::middleware('auth:api')->group(function () {
    
    Route::get('/me', [AuthController::class, 'me']); // Get user data
    Route::post('/logout', [AuthController::class, 'logout']); // Logout
    Route::delete('/delete', [AuthController::class, 'destroy']); // Delete account
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);// Route untuk refresh
    
    // Route untuk pelanggan
    Route::prefix('/v1/pelanggan')->middleware('api')->group(function () {
        Route::get('/', [PelangganController::class, 'index']);
        Route::get('/{id}', [PelangganController::class, 'show']);
        Route::post('/', [PelangganController::class, 'store']);
        Route::put('/{id}', [PelangganController::class, 'update']);
        Route::delete('/{id}', [PelangganController::class, 'destroy']);
    });
    
    //pelanggandetail
    Route::prefix('/v1/data/pelanggan')->middleware('api')->group(function (){
        Route::get("/",[PelangganDataController::class, 'index']);
        Route::get("/{id}",[PelangganDataController::class, 'show']);
        Route::post("/",[PelangganDataController::class, 'store']);
        Route::put("/{id}",[PelangganDataController::class, 'update']);
        Route::delete("/{id}",[PelangganDataController::class, 'destroy']);
    });
    
    // Route untuk admin
    Route::prefix('/v1/admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::post('/', [AdminController::class, 'store']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
        Route::put('/up/{id}', [AdminController::class, 'update_pass']);
        
    });
    
    // Route untuk kategori
    Route::prefix('/v1/kategori')->group(function () {
        Route::get('/', [KategoriController::class, 'index']);
        Route::get('/{id}', [KategoriController::class, 'show']);
        Route::post('/', [KategoriController::class, 'store']);
        Route::put('/{id}', [KategoriController::class, 'update']);
        Route::delete('/{id}', [KategoriController::class, 'destroy']);
    });
    
    // Route untuk alat
    Route::prefix('/v1/alat')->group(function () {
        Route::get('/', [AlatController::class, 'index']);
        Route::get('/{id}', [AlatController::class, 'show']);
        Route::post('/', [AlatController::class, 'store']);
        Route::put('/{id}', [AlatController::class, 'update']);
        Route::delete('/{id}', [AlatController::class, 'destroy']);
        Route::get('/image/{id}', [AlatController::class, 'showImage']); 
        
    });

    // Route untuk penyewaan
    Route::prefix('/v1/penyewaan')->group(function () {
        Route::get('/', [PenyewaanController::class, 'index']);
        Route::get('/{id}', [PenyewaanController::class, 'show']);
        Route::post('/', [PenyewaanController::class, 'store']);
        Route::put('/{id}', [PenyewaanController::class, 'update']);
        Route::delete('/{id}', [PenyewaanController::class, 'destroy']);
    });

    // Route untuk penyewaan detail
    Route::prefix('/v1/detail/penyewaan')->group(function () {
        Route::get('/', [PenyewaanDetailController::class, 'index']);
        Route::get('/{id}', [PenyewaanDetailController::class, 'show']);
        Route::post('/', [PenyewaanDetailController::class, 'store']);
        Route::put('/{id}', [PenyewaanDetailController::class, 'update']);
        Route::delete('/{id}', [PenyewaanDetailController::class, 'destroy']);
    });
});