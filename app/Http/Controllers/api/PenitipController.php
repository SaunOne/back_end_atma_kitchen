<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenitipController extends Controller
{
    public function showAll()
    {
        $penitips = Penitip::all();

        return response([
            'message' => 'All Penitip Retrieved',
            'data' => $penitips
        ], 200);
    }

    public function showById($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        return response([
            'message' => 'Show Penitip Successfully',
            'data' => $penitip
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_penitip' => 'required',
            'no_telp_penitip' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $penitip = Penitip::create($data);

        return response([
            'message' => 'Penitip created successfully',
            'data' => $penitip
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_penitip' => 'required',
            'no_telp_penitip' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $penitip->update($data);

        return response([
            'message' => 'Penitip updated successfully',
            'data' => $penitip
        ], 200);
    }

    public function destroy($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        $penitip->delete();

        return response(['message' => 'Penitip deleted successfully'], 200);
    }
}
