<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register',[App\Http\Controllers\Api\AuthController::class,'register'] );
Route::get('/verify/{verify_key}',[App\Http\Controllers\Api\AuthController::class,'verify'] );
Route::post('/login',[App\Http\Controllers\Api\AuthController::class,'login']);

Route::middleware('auth:api')->group(function(){
    Route::get('/',[App\Http\Controllers\Api\UserController::class,'fetchAll'] );
});
