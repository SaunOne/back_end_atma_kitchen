<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PembelianBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembelianBahanController extends Controller
{
    public function showAll()
    {
        $pembelianBahans = PembelianBahan::select('pembelian_bahan.*', 'bahan.*')
        ->join('bahan', 'pembelian_bahan.id_bahan', '=', 'bahan.id_bahan')
        ->get();

        return response([
            'message' => 'All Pembelian Bahan Retrieved',
            'data' => $pembelianBahans
        ], 200);
    }

    public function showById($id)
    {
        $pembelianBahan = PembelianBahan::find($id);

        if (!$pembelianBahan) {
            return response(['message' => 'Pembelian Bahan not found'], 404);
        }

        return response([
            'message' => 'Show Pembelian Bahan Successfully',
            'data' => $pembelianBahan
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_bahan' => 'required',
            'jumlah' => 'required',
            'harga_beli' => 'required',
            'tanggal' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $pembelianBahan = PembelianBahan::create($data);

        return response([
            'message' => 'Pembelian Bahan created successfully',
            'data' => $pembelianBahan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $pembelianBahan = PembelianBahan::find($id);

        if (!$pembelianBahan) {
            return response(['message' => 'Pembelian Bahan not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_bahan' => 'required',
            'jumlah' => 'required',
            'harga_beli' => 'required',
            'tanggal' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $pembelianBahan->update($data);

        return response([
            'message' => 'Pembelian Bahan updated successfully',
            'data' => $pembelianBahan
        ], 200);
    }

    public function destroy($id)
    {
        $pembelianBahan = PembelianBahan::find($id);

        if (!$pembelianBahan) {
            return response(['message' => 'Pembelian Bahan not found'], 404);
        }

        $pembelianBahan->delete();

        return response(['message' => 'Pembelian Bahan deleted successfully'], 200);
    }
}