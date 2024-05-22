<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    public function showAll()
    {
        $alamats = Alamat::all();

        return response([
            'message' => 'All Alamat Retrieved',
            'data' => $alamats
        ], 200);
    }

    public function showById($id)
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response(['message' => 'Alamat not found'], 404);
        }


        return response([
            'message' => 'Show Alamat Successfully',
            'data' => $alamat
        ], 200);
    }

    public function showByUser(){
        $id = auth()->User()->id_user;

        $alamats = Alamat::select()->where('id_user' , $id)->get();

        if($alamats->isEmpty()) {
            return response([
                "message" => "Alamat not found",
                
            ],404);
        }

        return response([
            "message" => "Show Alamat Successfully",
            "data" => $alamats,
        ],200);

    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['id_user'] = Auth()->id();
        $validate = Validator::make($data, [
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'kelurahan' => 'required',
            'detail_alamat' => 'required',
            'kode_pos' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $alamat = Alamat::create($data);

        return response([
            'message' => 'Alamat created successfully',
            'data' => $alamat
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response(['message' => 'Alamat not found'], 404);
        }

        $data = $request->all();
        
        $validate = Validator::make($data, [
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'kelurahan' => 'required',
            'detail_alamat' => 'required',
            'kode_pos' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $alamat->update($data);

        return response([
            'message' => 'Alamat updated successfully',
            'data' => $alamat
        ], 200);
    }

    public function destroy($id)
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response(['message' => 'Alamat not found'], 404);
        }

        $alamat->delete();

        return response(['message' => 'Alamat deleted successfully'], 200);
    }
}
