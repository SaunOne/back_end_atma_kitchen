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
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::get('/verify/{verify_key}', [App\Http\Controllers\Api\AuthController::class, 'verify']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::get('/cek-active/{id}', [App\Http\Controllers\Api\AuthController::class, 'cekActive']);

//forget password
Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword'])->middleware('guest')->name('password.email');
Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword'])->middleware('guest')->name('password.update');


Route::get('/test', [App\Http\Controllers\Api\UserController::class, 'test']);

Route::middleware('auth:api')->group(function(){
    Route::get('/',[App\Http\Controllers\Api\UserController::class,'fetchAll'] );
});