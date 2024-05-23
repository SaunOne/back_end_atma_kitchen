<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    public function showAll(){
        $data = Keranjang::all();

        if (!$data) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            "message" => "Show All Data Successfully",
            "data" => $data
        ]);
    }

    public function showByUser(){
        $id = Auth::user()->id;
        $data = Keranjang::select()->where('id_user',$id)->get();

        if (!$data) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            "message" => "Show All Data Successfully",
            "data" => $data
        ]);
    }

    public function update(Request $request,$id){

        $data = $request->all();

        $keranjang = Keranjang::find($id);

        if (!$keranjang) {
            return response(['message' => 'Produk not found'], 404);
        }

        $keranjang->update([
            $data
        ]);

        return response([
            "message" => "update successfully",
            "data" => $keranjang
        ]);
    }

    public function keranjang(Request $request){
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_user' => 'required',
            'jumlah' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $keranjang = Keranjang::create($data);

        return response([
            "message" => "update successfully",
            "data" => $keranjang
        ]);
    }

    public function destroy($id)
    {
        $keranjang = Keranjang::find($id);

        if (!$keranjang) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        $keranjang->delete();
        return response(['message' => 'Pegawai deleted successfully'], 200);
    }
}
