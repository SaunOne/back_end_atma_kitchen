<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function showAll()
    {
        $transaksis = Transaksi::select();

        return response([
            'message' => 'All Transaksis Retrieved',
            'data' => $transaksis
        ], 200);
    }

    public function showByUser()
    {
        $id_user =  Auth::user()->id_user;
        $transaksis = Transaksi::select('transaksi.*','u.name_lengkap')
        ->join('users', 'users.user_id', 'transaksi.user_id')
        ->where('transaksi.id_user', $id_user)->get();
        foreach ($transaksis as $transaksi) {
            $detail_transaksis = DetailTransaksi::where('id_transaksi', $transaksi->id_transaksi)->get();
            $transaksi->detail_transaksi = $detail_transaksis;
            $transaksi->alamat = Alamat::where('id_alamat',$transaksi->id_alamat)->first();
            foreach ($detail_transaksis as $detail_transaksi){
                $products = Produk::where('id_produk',$detail_transaksi->id_produk)->first();
                $detail_transaksi->produk = $products;
            }
        }

        return response([
            'message' => 'All Transaksis Retrieved',
            'data' => $transaksis,
        ], 200);
    }

    public function showById($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        return response([
            'message' => 'Show Transaksi Successfully',
            'data' => $transaksi
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi = Transaksi::create($data);

        return response([
            'message' => 'Transaksi created successfully',
            'data' => $transaksi
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi->update($data);

        return response([
            'message' => 'Transaksi updated successfully',
            'data' => $transaksi
        ], 200);
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        $transaksi->delete();

        return response(['message' => 'Transaksi deleted successfully'], 200);
    }
}
