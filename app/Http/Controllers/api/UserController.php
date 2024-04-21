<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function showAll(){

        $user = User::all();

        return response()->json($user);
    }

  


    public function findByIdUser(){

    }

    public function test(){
        $user = DB::table('password_reset_tokens')->select('token')->where('email','tinartinar720@gmail.com')->value('column');
        
        return response([
            $user
        ]);
    }
}
