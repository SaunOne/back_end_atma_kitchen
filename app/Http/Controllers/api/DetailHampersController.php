<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailHampers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailHampersController extends Controller
{
    public function showAll()
    {
        $details = DetailHampers::all();

        return response([
            'message' => 'All Detail Hampers Retrieved',
            'data' => $details
        ], 200);
    }

    public function showById($id)
    {
        $detail = DetailHampers::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Hampers not found'], 404);
        }

        return response([
            'message' => 'Show Detail Hampers Successfully',
            'data' => $detail
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'jumlah_produk' => 'required',
            'id_hampers' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $detail = DetailHampers::create($data);

        return response([
            'message' => 'Detail Hampers created successfully',
            'data' => $detail
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $detail = DetailHampers::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Hampers not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'jumlah_produk' => 'required',
            'id_hampers' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $detail->update($data);

        return response([
            'message' => 'Detail Hampers updated successfully',
            'data' => $detail
        ], 200);
    }

    public function destroy($id)
    {
        $detail = DetailHampers::find($id);

        if (!$detail) {
            return response(['message' => 'Detail Hampers not found'], 404);
        }

        $detail->delete();

        return response(['message' => 'Detail Hampers deleted successfully'], 200);
    }
}
