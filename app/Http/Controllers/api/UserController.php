<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showAll(){

        $user = User::all();

        return response()->json($user);
    }

  


    public function findByIdUser(){

    }
}
