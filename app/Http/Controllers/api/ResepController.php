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

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
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

    public function update(Request $request, $id)
    {
        $resep = Resep::find($id);

        if (!$resep) {
            return response(['message' => 'Resep not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $resep->update($data);

        return response([
            'message' => 'Resep updated successfully',
            'data' => $resep
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
}
