<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    public function showAll()
    {
        $pegawais = Pegawai::all();

        return response([
            'message' => 'All Pegawai Retrieved',
            'data' => $pegawais
        ], 200);
    }

    public function showById($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        return response([
            'message' => 'Show Pegawai Successfully',
            'data' => $pegawai
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'gaji' => 'required',
            'bonus_gaji' => 'required',
            'jabatan' => 'required',
            'id_user' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $pegawai = Pegawai::create($data);

        return response([
            'message' => 'Pegawai created successfully',
            'data' => $pegawai
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        $data = $request->all();

        $pegawai->update($data);

        return response([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai
        ], 200);
    }

    public function updateGajiBonus(Request $request,$id){
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'gaji' => 'required',
            'bonus_gaji' => 'required',
        ]);

        $data = $request->all();

        $pegawai->update($data);

        return response([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai
        ], 200);
        
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        $pegawai->delete();

        return response(['message' => 'Pegawai deleted successfully'], 200);
    }


}
