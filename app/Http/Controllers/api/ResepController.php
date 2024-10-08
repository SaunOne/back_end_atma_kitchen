<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResepController extends Controller
{
    public function showAll()
    {
        $reseps = Resep::all();

        return response([
            'message' => 'All Reseps Retrieved',
            'data' => $reseps
        ], 200);
    }

    public function showById($id)
    {
        $resep = Resep::find($id);

        if (!$resep) {
            return response(['message' => 'Resep not found'], 404);
        }

        return response([
            'message' => 'Show Resep Successfully',
            'data' => $resep
        ], 200);
    }
    
    public function showByIdProduk($id)
    {
        $resep = Resep::where('id_produk',$id)->get();

        if (!$resep) {
            return response(['message' => 'Resep not found'], 404);
        }

        return response([
            'message' => 'Show Resep Successfully',
            'data' => $resep
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_bahan' => 'required',
            'jumlah_bahan' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $resep = Resep::create($data);

        return response([
            'message' => 'Resep created successfully',
            'data' => $resep
        ], 200);
    }

    public function storeAll(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'reseps' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        
        foreach($data['reseps'] as $r){
           Resep::create($r);
        }

        return response([
            'message' => 'Resep created successfully',
            'data' => $data
        ], 200);
    }

    public function destroy($id)
    {
        $resep = Resep::find($id);

        if (!$resep) {
            return response(['message' => 'Resep not found'], 404);
        }

        $resep->delete();

        return response(['message' => 'Resep deleted successfully'], 200);
    }

    public function destroyByIdProduk($id)
    {
        $resep = Resep::find($id);

        if (!$resep) {
            return response(['message' => 'Resep not found'], 404);
        }

        $resep->delete();

        return response(['message' => 'Resep deleted successfully'], 200);
    }
}
