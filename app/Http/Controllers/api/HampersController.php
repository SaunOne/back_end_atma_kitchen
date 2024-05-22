<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Hampers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HampersController extends Controller
{
    public function showAll()
    {
        $hampers = Hampers::all();

        return response([
            'message' => 'All Hampers Retrieved',
            'data' => $hampers
        ], 200);
    }

    // public function cekLimitHampers($tanggal){
    //     $jumlah_sisa = DB::table('detail_hampers as dt')
    //         ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
    //         ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
    //         ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
    //         ->where('dt.id_hampers', $produk['id_produk'])
    //         ->where('lo.tanggal', '2024-5-19') //nanti inget ganti ke now()
    //         ->min('lo.jumlah_sisa');
    // }

    public function showById($id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        return response([
            'message' => 'Show Hampers Successfully',
            'data' => $hampers
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_packaging' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $hampers = Hampers::create($data);

        return response([
            'message' => 'Hampers created successfully',
            'data' => $hampers
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_packaging' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $hampers->update($data);

        return response([
            'message' => 'Hampers updated successfully',
            'data' => $hampers
        ], 200);
    }

    public function destroy($id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        $hampers->delete();

        return response(['message' => 'Hampers deleted successfully'], 200);
    }
}