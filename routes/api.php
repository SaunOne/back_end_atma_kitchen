<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware; 

//umum

Route::post('/register',[App\Http\Controllers\Api\AuthController::class,'register'] );
Route::get('/verify/{verify_key}',[App\Http\Controllers\Api\AuthController::class,'verify'] );
Route::post('/login',[App\Http\Controllers\Api\AuthController::class,'login']);

//forget password
Route::post('/forgot-password',[App\Http\Controllers\Api\AuthController::class,'forgotPassword'])->middleware('guest')->name('password.email');
Route::post('/reset-password',[App\Http\Controllers\Api\AuthController::class,'resetPassword'] )->middleware('guest')->name('password.update');

//test
Route::get('/test',[App\Http\Controllers\Api\UserController::class,'test']);



Route::middleware(['auth:api','owner'])->group(function(){
    

});

Route::middleware(['auth:api','MO'])->group(function(){

    Route::get('/',[App\Http\Controllers\Api\UserController::class,'fetchAll'] );
     //Absensi
    Route::get('/absensi',[App\Http\Controllers\Api\AbsensiController::class,'showAll']);
     //Pegawai
    Route::put('/absensi/${id}',[App\Http\Controllers\Api\AbsensiController::class,'updatePassword']);

     //kelola data karyawan
     Route::get('/karyawan',[App\Http\Controllers\api\PegawaiController::class,'showAll']);
     Route::get('/karyawan/{id}',[App\Http\Controllers\api\PegawaiController::class,'showById']); 
     Route::post('/karyawan',[App\Http\Controllers\api\PegawaiController::class,'store']);
     Route::put('/karyawan/{id}',[App\Http\Controllers\api\PegawaiController::class,'update']);
     Route::delete('/karyawan/{id}',[App\Http\Controllers\api\PegawaiController::class,'destroy']);
 
     //mengubah data gaji dan bonus
     Route::put('/karyawan/update_gaji_bonus/{id}',[App\Http\Controllers\api\PegawaiController::class,'updateGajiBonus']);
});

Route::middleware(['auth:api','admin'])->group(function(){
 
     //Produk
     Route::get('/produk',[App\Http\Controllers\api\ProdukController::class,'showAll']);
     Route::get('/produk/search/{id}',[App\Http\Controllers\api\ProdukController::class,'showById']);
     Route::get('/produk/searchAll/{id}',[App\Http\Controllers\api\ProdukController::class,'searchProduk']);
     Route::post('/produk',[App\Http\Controllers\api\ProdukController::class,'store']);
     Route::put('/produk',[App\Http\Controllers\api\ProdukController::class,'searchProduk']);
     Route::delete('/produk',[App\Http\Controllers\api\ProdukController::class,'destroy']);
 
     //resep
     Route::get('/resep',[App\Http\Controllers\api\ResepController::class,'showAll']);
     Route::get('/resep/{id}',[App\Http\Controllers\api\ResepController::class,'showById']);
     Route::get('/resep/produk/{id}',[App\Http\Controllers\api\ResepController::class,'showByIdProduk']); 
     Route::post('/resep',[App\Http\Controllers\api\ResepController::class,'store']);
     Route::post('/resep/produk',[App\Http\Controllers\api\ResepController::class,'storeAll']);
     Route::put('/resep/{id}',[App\Http\Controllers\api\ResepController::class,'storeByProduk']);
     Route::delete('/resep/{id}',[App\Http\Controllers\api\ResepController::class,'destroy']);
 
     //kelola bahan baku
     Route::get('/bahan',[App\Http\Controllers\api\BahanController::class,'showAll']);
     Route::get('/bahan/{id}',[App\Http\Controllers\api\BahanController::class,'showById']); 
     Route::post('/bahan',[App\Http\Controllers\api\BahanController::class,'store']);
     Route::put('/bahan/{id}',[App\Http\Controllers\api\BahanController::class,'update']);
     Route::delete('/bahan/{id}',[App\Http\Controllers\api\BahanController::class,'destroy']);
 
     //kelola hampers
     Route::get('/hampers',[App\Http\Controllers\api\HampersController::class,'showAll']);
     Route::get('/hampers/{id}',[App\Http\Controllers\api\HampersController::class,'showById']); 
     Route::post('/hampers',[App\Http\Controllers\api\HampersController::class,'store']);
     Route::put('/hampers/{id}',[App\Http\Controllers\api\HampersController::class,'update']);
     Route::delete('/hampers/{id}',[App\Http\Controllers\api\HampersController::class,'destroy']);
 
     //penitip
     Route::get('/penitip',[App\Http\Controllers\api\PegawaiController::class,'showAll']);
     Route::get('/penitip/{id}',[App\Http\Controllers\api\PegawaiController::class,'showById']); 
     Route::post('/penitip',[App\Http\Controllers\api\PegawaiController::class,'store']);
     Route::put('/penitip/{id}',[App\Http\Controllers\api\PegawaiController::class,'update']);
     Route::delete('/penitip/{id}',[App\Http\Controllers\api\PegawaiController::class,'destroy']);
 
     //Produk Utama
     
    

});

Route::middleware(['auth:api','karyawan'])->group(function(){


});



    

 