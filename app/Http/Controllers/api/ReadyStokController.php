<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ReadyStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReadyStokController extends Controller
{
    public function showAll()
    {
        $readyStoks = ReadyStok::all();

        return response([
            'message' => 'All Ready Stok Retrieved',
            'data' => $readyStoks
        ], 200);
    }

    public function showById($id)
    {
        $readyStok = ReadyStok::find($id);

        if (!$readyStok) {
            return response(['message' => 'Ready Stok not found'], 404);
        }

        return response([
            'message' => 'Show Ready Stok Successfully',
            'data' => $readyStok
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'jumlah_stok' => 'required',
            'satuan' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $readyStok = ReadyStok::create($data);

        return response([
            'message' => 'Ready Stok created successfully',
            'data' => $readyStok
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $readyStok = ReadyStok::find($id);

        if (!$readyStok) {
            return response(['message' => 'Ready Stok not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'jumlah_stok' => 'required',
            'satuan' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $readyStok->update($data);

        return response([
            'message' => 'Ready Stok updated successfully',
            'data' => $readyStok
        ], 200);
    }

    public function destroy($id)
    {
        $readyStok = ReadyStok::find($id);

        if (!$readyStok) {
            return response(['message' => 'Ready Stok not found'], 404);
        }

        $readyStok->delete();

        return response(['message' => 'Ready Stok deleted successfully'], 200);
    }
}
