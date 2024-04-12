<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProdukUtama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukUtamaController extends Controller
{
    public function showAll()
    {
        $produkUtamas = ProdukUtama::all();

        return response([
            'message' => 'All Produk Utama Retrieved',
            'data' => $produkUtamas
        ], 200);
    }

    public function showById($id)
    {
        $produkUtama = ProdukUtama::find($id);

        if (!$produkUtama) {
            return response(['message' => 'Produk Utama not found'], 404);
        }

        return response([
            'message' => 'Show Produk Utama Successfully',
            'data' => $produkUtama
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_packaging' => 'required',
            'katagori_produk' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produkUtama = ProdukUtama::create($data);

        return response([
            'message' => 'Produk Utama created successfully',
            'data' => $produkUtama
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $produkUtama = ProdukUtama::find($id);

        if (!$produkUtama) {
            return response(['message' => 'Produk Utama not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_packaging' => 'required',
            'katagori_produk' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produkUtama->update($data);

        return response([
            'message' => 'Produk Utama updated successfully',
            'data' => $produkUtama
        ], 200);
    }

    public function destroy($id)
    {
        $produkUtama = ProdukUtama::find($id);

        if (!$produkUtama) {
            return response(['message' => 'Produk Utama not found'], 404);
        }

        $produkUtama->delete();

        return response(['message' => 'Produk Utama deleted successfully'], 200);
    }
}
