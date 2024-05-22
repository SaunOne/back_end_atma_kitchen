<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Alamat;
use App\Models\Pegawai;
use App\Models\DetailTransaksi;
use App\Models\Alamat;
use App\Models\Pegawai;
use App\Models\DetailTransaksi;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function fetchAll(){

        $user = User::all();

        return response()->json($user);
    }


    public function findByIdUser(){

    }
}