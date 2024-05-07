<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    public function showAll()
    {
        $absensi = Absensi::select('absensi.*', 'pegawai.*')
        ->join('pegawai', 'absensi.id_user', '=', 'pegawai.id_user')
        ->get();
  
        return response([
            'message' => 'All Absensi Retrieved',
            'data' => $absensi
        ], 200);
    }

    public function showById($id)
    {
        $absensi = Absensi::find($id);

        if (!$absensi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        return response([
            'message' => 'Show Absensi Successfully',
            'data' => $absensi
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_user' => 'required',
            'tanggal' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $absensi = Absensi::create($data);

        return response([
            'message' => 'Absensi created successfully',
            'data' => $absensi
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $absensi = Absensi::find($id);

        if (!$absensi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'tanggal' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $absensi->update($data);

        return response([
            'message' => 'Absensi updated successfully',
            'data' => $absensi
        ], 200);
    }

    public function destroy($id)
    {
        $absensi = Absensi::find($id);

        if (!$absensi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        $absensi->delete();

        return response(['message' => 'Absensi deleted successfully'], 200);
    }
}
