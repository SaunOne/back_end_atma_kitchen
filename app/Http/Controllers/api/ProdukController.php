<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function showAll()
    {
        $produks = Produk::all();

        return response([
            'message' => 'All Produk Retrieved',
            'data' => $produks
        ], 200);
    }

    public function showById($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            'message' => 'Show Produk Successfully',
            'data' => $produk
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_stok_produk' => 'required',
            'nama_produk' => 'required',
            'harga' => 'required',
            'quantity' => 'required',
            'deskripsi' => 'required',
            'jenis_produk' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produk = Produk::create($data);

        return response([
            'message' => 'Produk created successfully',
            'data' => $produk
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_stok_produk' => 'required',
            'nama_produk' => 'required',
            'harga' => 'required',
            'quantity' => 'required',
            'deskripsi' => 'required',
            'jenis_produk' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produk->update($data);

        return response([
            'message' => 'Produk updated successfully',
            'data' => $produk
        ], 200);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $produk->delete();

        return response(['message' => 'Produk deleted successfully'], 200);
    }
}
