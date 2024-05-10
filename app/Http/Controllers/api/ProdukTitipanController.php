<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProdukTitipan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukTitipanController extends Controller
{
    public function showAll()
    {   
        $produkTitipans = Produk::select('produk.*','produk_titipan.*')
        ->join('produk_titipan','produk_titipan.id_produk','produk.id_produk')
        ->where('produk.jenis_produk','Titipan')
        ->get();
        

        return response([       
            'message' => 'All Produk Titipan Retrieved',
            'data' => $produkTitipans
        ], 200);
    }

    public function showById($id)
    {
        $produkTitipan = ProdukTitipan::find($id);

        if (!$produkTitipan) {
            return response(['message' => 'Produk Titipan not found'], 404);
        }

        return response([
            'message' => 'Show Produk Titipan Successfully',
            'data' => $produkTitipan
        ], 200);
    }

    public function store(Request $request)
    {   $data = $request->all();
        return response([
            "message" => $data,
        ]);
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_penitip' => 'required',
            'jumlah_produk_dititip',
            'tanggal' => 'tanggal'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produkTitipan = ProdukTitipan::create($data);

        return response([
            'message' => 'Produk Titipan created successfully',
            'data' => $produkTitipan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $produkTitipan = ProdukTitipan::find($id);

        if (!$produkTitipan) {
            return response(['message' => 'Produk Titipan not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_penitip' => 'required',
            'jumlah_produk_dititip',
            'tanggal' => 'tanggal'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produkTitipan->update($data);

        return response([
            'message' => 'Produk Titipan updated successfully',
            'data' => $produkTitipan
        ], 200);
    }

    public function destroy($id)
    {
        $produkTitipan = ProdukTitipan::find($id);

        if (!$produkTitipan) {
            return response(['message' => 'Produk Titipan not found'], 404);
        }

        $produkTitipan->delete();

        return response(['message' => 'Produk Titipan deleted successfully'], 200);
    }
}
