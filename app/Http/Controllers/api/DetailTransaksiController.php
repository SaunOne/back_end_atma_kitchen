<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailTransaksiController extends Controller
{
    public function showAll()
    {
        $details = DetailTransaksi::all();

        return response([
            'message' => 'All Detail Transaksi Retrieved',
            'data' => $details
        ], 200);
    }

    public function showById($id)
    {
        $detail = DetailTransaksi::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Transaksi not found'], 404);
        }

        return response([
            'message' => 'Show Detail Transaksi Successfully',
            'data' => $detail
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_transaksi' => 'required',
            'jumlah_produk' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $detail = DetailTransaksi::create($data);

        return response([
            'message' => 'Detail Transaksi created successfully',
            'data' => $detail
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $detail = DetailTransaksi::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Transaksi not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_transaksi' => 'required',
            'jumlah_produk' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $detail->update($data);

        return response([
            'message' => 'Detail Transaksi updated successfully',
            'data' => $detail
        ], 200);
    }

    public function destroy($id)
    {
        $detail = DetailTransaksi::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Transaksi not found'], 404);
        }

        $detail->delete();

        return response(['message' => 'Detail Transaksi deleted successfully'], 200);
    }
}
