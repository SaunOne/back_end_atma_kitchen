<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware;

//umum

Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::get('/verify/{verify_key}', [App\Http\Controllers\Api\AuthController::class, 'verify']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::get('/cek-active/{id}', [App\Http\Controllers\Api\AuthController::class, 'cekActive']);

//forget password
Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword'])->middleware('guest')->name('password.email');
Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword'])->middleware('guest')->name('password.update');


Route::get('/test', [App\Http\Controllers\Api\UserController::class, 'test']);

Route::middleware(['auth:api', 'owner'])->group(function () {
});

Route::middleware(['auth:api'])->group(function () {

     Route::get('/', [App\Http\Controllers\Api\UserController::class, 'fetchAll']);
     Route::get('/absensi', [App\Http\Controllers\Api\AbsensiController::class, 'showAll']);
     Route::post('/absensi', [App\Http\Controllers\Api\AbsensiController::class, 'store']);
     Route::put('/absensi/{id}', [App\Http\Controllers\Api\AbsensiController::class, 'update']);
     Route::delete('/absensi/{id}', [App\Http\Controllers\Api\AbsensiController::class, 'destroy']);

     //kelola data karyawan
     Route::get('/karyawan', [App\Http\Controllers\api\PegawaiController::class, 'showAll']);
     Route::get('/karyawan/{id}', [App\Http\Controllers\api\PegawaiController::class, 'showById']);
     Route::post('/karyawan', [App\Http\Controllers\api\PegawaiController::class, 'store']);
     Route::put('/karyawan/{id}', [App\Http\Controllers\api\PegawaiController::class, 'update']);
     Route::delete('/karyawan/{id}', [App\Http\Controllers\api\PegawaiController::class, 'destroy']);

     //mengubah data gaji dan bonus
     Route::put('/karyawan/update_gaji_bonus/{id}', [App\Http\Controllers\api\PegawaiController::class, 'updateGajiBonus']);

     //pembelian bahan
     Route::get('/pembelian-bahan', [App\Http\Controllers\api\PembelianBahanController::class, 'showAll']);
     Route::get('/pembelian-bahan/{id}', [App\Http\Controllers\api\PembelianBahanController::class, 'showById']);
     Route::post('/pembelian-bahan', [App\Http\Controllers\api\PembelianBahanController::class, 'store']);
     Route::put('/pembelian-bahan/{id}', [App\Http\Controllers\api\PembelianBahanController::class, 'update']);
     Route::delete('/pembelian-bahan/{id}', [App\Http\Controllers\api\PembelianBahanController::class, 'destroy']);

     //pengeluaran lain lain
     Route::get('/pengeluaran-lain-lain', [App\Http\Controllers\api\PengeluaranLainLainController::class, 'showAll']);
     Route::get('/pengeluaran-lain-lain/{id}', [App\Http\Controllers\api\PengeluaranLainLainController::class, 'showById']);
     Route::post('/pengeluaran-lain-lain', [App\Http\Controllers\api\PengeluaranLainLainController::class, 'store']);
     Route::put('/pengeluaran-lain-lain/{id}', [App\Http\Controllers\api\PengeluaranLainLainController::class, 'update']);
     Route::delete('/pengeluaran-lain-lain/{id}', [App\Http\Controllers\api\PengeluaranLainLainController::class, 'destroy']);

});

Route::middleware(['auth:api', 'admin'])->group(function () {

     //Produk
     Route::get('/produk', [App\Http\Controllers\api\ProdukController::class, 'showAll']);
     Route::get('/produk/search/{id}', [App\Http\Controllers\api\ProdukController::class, 'showById']);
     Route::get('/produk/searchAll/{id}', [App\Http\Controllers\api\ProdukController::class, 'searchProduk']);
     Route::post('/produk', [App\Http\Controllers\api\ProdukController::class, 'store']);
     Route::put('/produk', [App\Http\Controllers\api\ProdukController::class, 'searchProduk']);
     Route::delete('/produk', [App\Http\Controllers\api\ProdukController::class, 'destroy']);

     //produk-utama
     Route::get('/produk-utama', [App\Http\Controllers\api\ProdukUtamaController::class, 'showAll']);

     //resep
     Route::get('/resep', [App\Http\Controllers\api\ResepController::class, 'showAll']);
     Route::get('/resep/{id}', [App\Http\Controllers\api\ResepController::class, 'showById']);
     Route::get('/resep/produk/{id}', [App\Http\Controllers\api\ResepController::class, 'showByIdProduk']);
     Route::post('/resep', [App\Http\Controllers\api\ResepController::class, 'store']);
     Route::post('/resep/produk', [App\Http\Controllers\api\ResepController::class, 'storeAll']);
     Route::put('/resep/{id}', [App\Http\Controllers\api\ResepController::class, 'storeByProduk']);
     Route::delete('/resep/{id}', [App\Http\Controllers\api\ResepController::class, 'destroy']);

     //kelola bahan baku
     Route::get('/bahan', [App\Http\Controllers\api\BahanController::class, 'showAll']);
     Route::get('/bahan/{id}', [App\Http\Controllers\api\BahanController::class, 'showById']);
     Route::post('/bahan', [App\Http\Controllers\api\BahanController::class, 'store']);
     Route::put('/bahan/{id}', [App\Http\Controllers\api\BahanController::class, 'update']);
     Route::delete('/bahan/{id}', [App\Http\Controllers\api\BahanController::class, 'destroy']);

     //kelola hampers
     Route::get('/hampers', [App\Http\Controllers\api\HampersController::class, 'showAll']);
     Route::get('/hampers/{id}', [App\Http\Controllers\api\HampersController::class, 'showById']);
     Route::post('/hampers', [App\Http\Controllers\api\HampersController::class, 'store']);
     Route::put('/hampers/{id}', [App\Http\Controllers\api\HampersController::class, 'update']);
     Route::delete('/hampers/{id}', [App\Http\Controllers\api\HampersController::class, 'destroy']);

     //penitip
     Route::get('/penitip', [App\Http\Controllers\api\PenitipController::class, 'showAll']);
     Route::get('/penitip/{id}', [App\Http\Controllers\api\PenitipController::class, 'showById']);
     Route::post('/penitip', [App\Http\Controllers\api\PenitipController::class, 'store']);
     Route::put('/penitip/{id}', [App\Http\Controllers\api\PenitipController::class, 'update']);
     Route::delete('/penitip/{id}', [App\Http\Controllers\api\PenitipController::class, 'destroy']);

     //Produk Utama

     //penitip
     Route::get('/produk-titipan', [App\Http\Controllers\api\ProdukTitipanController::class, 'showAll']);
     Route::get('/produk-titipan/{id}', [App\Http\Controllers\api\ProdukTitipanController::class, 'showById']);
     Route::post('/produk-titipan', [App\Http\Controllers\api\ProdukTitipanController::class, 'store']);
     Route::put('/produk-titipan/{id}', [App\Http\Controllers\api\ProdukTitipanController::class, 'update']);
     Route::delete('/produk-titipan/{id}', [App\Http\Controllers\api\ProdukTitipanController::class, 'destroy']);

     //penitip
     Route::get('/ready-stok', [App\Http\Controllers\api\ReadyStokController::class, 'showAll']);
     Route::get('/ready-stok/{id}', [App\Http\Controllers\api\ReadyStokController::class, 'showById']);
     Route::post('/ready-stok', [App\Http\Controllers\api\ReadyStokController::class, 'store']);
     Route::put('/ready-stok/{id}', [App\Http\Controllers\api\ReadyStokController::class, 'update']);
     Route::delete('/ready-stok/{id}', [App\Http\Controllers\api\ReadyStokController::class, 'destroy']);

     //packaging
     Route::get('/packaging', [App\Http\Controllers\api\PackagingController::class, 'showAll']);
     Route::get('/packaging/{id}', [App\Http\Controllers\api\PackagingController::class, 'showById']);
     Route::post('/packaging', [App\Http\Controllers\api\PackagingController::class, 'store']);
     Route::put('/packaging/{id}', [App\Http\Controllers\api\PackagingController::class, 'update']);
     Route::delete('/packaging/{id}', [App\Http\Controllers\api\PackagingController::class, 'destroy']);

     
     



});

Route::middleware(['auth:api', 'customer'])->group(function () {
     Route::get('/user-profile', [App\Http\Controllers\api\UserController::class, 'getProfile']);
     Route::get('/user-auth', [App\Http\Controllers\api\UserController::class, 'findByAuth']);
     Route::post('/user/update-profile', [App\Http\Controllers\api\UserController::class, 'updateProfile']);

     //transaksi
     Route::get('/transaksi',[App\http\Controllers\api\TransaksiController::class,'showByUser']);
});