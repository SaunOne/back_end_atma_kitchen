<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function showAll()
    {
        $transaksis = Transaksi::all();

        return response([
            'message' => 'All Transaksis Retrieved',
            'data' => $transaksis
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
