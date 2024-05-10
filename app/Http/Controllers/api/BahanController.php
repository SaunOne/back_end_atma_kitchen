<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BahanController extends Controller
{
    public function showAll()
    {
        $bahans = Bahan::all();

        return response([
            'message' => 'All Bahan Retrieved',
            'data' => $bahans
        ], 200);
    }

    public function showById($id)
    {
        $bahan = Bahan::find($id);

        if (!$bahan) {
            return response(['message' => 'Bahan not found'], 404);
        }

        return response([
            'message' => 'Show Bahan Successfully',
            'data' => $bahan
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_bahan' => 'required',
            'stok_bahan' => 'nullable',
            'satuan' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        
        $bahan = Bahan::create([
            'nama_bahan' => $data['nama_bahan'],
            'stok_bahan' => 0,
            'satuan' => $data['satuan'],
        ]);

        return response([
            'message' => 'Bahan created successfully',
            'data' => $bahan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $bahan = Bahan::find($id);

        if (!$bahan) {
            return response(['message' => 'Bahan not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_bahan' => 'required',
            'stok_bahan' => 'nullable',
            'satuan' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $bahan->update($data);

        return response([
            'message' => 'Bahan updated successfully',
            'data' => $bahan
        ], 200);
    }

    public function destroy($id)
    {
        $bahan = Bahan::find($id);

        if (!$bahan) {
            return response(['message' => 'Bahan not found'], 404);
        }

        $bahan->delete();

        return response(['message' => 'Bahan deleted successfully'], 200);
    }
}
