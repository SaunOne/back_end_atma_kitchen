<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keranjang;

class KeranjangController extends Controller
{
    public function showAll(){
        $data = Keranjang::all();

        return response([
            "message" => "Show All Data Successfully",
            "data" => $data
        ]);
    }
}
