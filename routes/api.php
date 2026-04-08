<?php

use App\Http\Controllers\API\AktivasiController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BarangController;
use App\Http\Controllers\API\BarangPengajuanController;
use App\Http\Controllers\API\BarangPengajuanLainnyaController;
use App\Http\Controllers\API\DaftarPengajuanController;
use App\Http\Controllers\API\JabatanController;
use App\Http\Controllers\API\PengajuanController;
use App\Http\Controllers\API\PengumumanController;
use App\Http\Controllers\API\SwaggerTestController;
use App\Http\Controllers\API\UnitTypeController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VendorController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('swagger-test', [SwaggerTestController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/profile', [UserController::class, 'updateProfile']);

    Route::get('/periode-aktif', [AktivasiController::class, 'current']);

    
    Route::apiResource(
        'daftar-pengajuan',
        DaftarPengajuanController::class
    );
    
    Route::post(
        'barang-pengajuan',
        [BarangPengajuanController::class, 'store']
    );
    
    Route::patch(
        'barang-pengajuan/{barangPengajuan}',
        [BarangPengajuanController::class, 'updateJumlah']
    );
    
    Route::delete(
        'barang-pengajuan/{barangPengajuan}',
        [BarangPengajuanController::class, 'destroy']
    );
    
    Route::post(
        'barang-pengajuan-lainnya',
        [BarangPengajuanLainnyaController::class, 'store']
    );
    
    Route::get(
        'my-pengajuan',
        [PengajuanController::class, 'my']
    );
    
    Route::patch(
        'barang-pengajuan-lainnya/{barangPengajuanLainnya}',
        [BarangPengajuanLainnyaController::class, 'updateJumlah']
    );

    Route::delete(
        'barang-pengajuan-lainnya/{barangPengajuanLainnya}',
        [BarangPengajuanLainnyaController::class, 'destroy']
    );
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::apiResource('users', UserController::class);
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('barang', BarangController::class);

    Route::apiResource(
        'aktivasi-pengajuan',
        AktivasiController::class
    );

    Route::patch(
        'aktivasi-pengajuan/{aktivasiPengajuan}/activate',
        [AktivasiController::class, 'activate']
    );

    Route::apiResource(
        'pengumuman',
        PengumumanController::class
    );

    Route::apiResource(
        'jabatans',
        JabatanController::class
    );

    Route::apiResource(
        'unit-types',
        UnitTypeController::class
    );

    Route::get(
        'admin/daftar-pengajuan',
        [DaftarPengajuanController::class, 'adminIndex']
    );

    Route::patch(
        'admin/barang-pengajuan/{barangPengajuan}/approve',
        [BarangPengajuanController::class, 'approve']
    );

    Route::patch(
        'admin/barang-pengajuan-lainnya/{barangPengajuanLainnya}/approve',
        [BarangPengajuanLainnyaController::class, 'approve']
    );
});
