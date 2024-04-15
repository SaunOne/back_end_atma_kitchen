<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;



Route::post('/register',[App\Http\Controllers\Api\AuthController::class,'register'] );
Route::get('/verify/{verify_key}',[App\Http\Controllers\Api\AuthController::class,'verify'] );
Route::post('/login',[App\Http\Controllers\Api\AuthController::class,'login']);

//forget password
Route::post('/forgot-password',[App\Http\Controllers\Api\AuthController::class,'forgotPassword'])->middleware('guest')->name('password.email');
Route::post('/reset-password',[App\Http\Controllers\Api\AuthController::class,'resetPassword'] )->middleware('guest')->name('password.update');

//test
Route::get('/test',[App\Http\Controllers\Api\UserController::class,'test']);



//
Route::middleware('auth:api')->group(function(){
        
    Route::get('/',[App\Http\Controllers\Api\UserController::class,'fetchAll'] );
    //Absensi
    Route::get('/absensi',[App\Http\Controllers\Api\AbsensiController::class,'showAll']);

    //Pegawai
    Route::put('/absensi/${id}',[App\Http\Controllers\Api\AbsensiController::class,'updatePassword']);

});
