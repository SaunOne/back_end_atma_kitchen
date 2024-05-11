<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Hampers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HampersController extends Controller
{
    public function showAll()
    {
        $hampers = Hampers::all();

        return response([
            'message' => 'All Hampers Retrieved',
            'data' => $hampers
        ], 200);
    }

    public function showById($id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        return response([
            'message' => 'Show Hampers Successfully',
            'data' => $hampers
        ], 200);
    }


    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_packaging' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $hampers = Hampers::create($data);

        return response([
            'message' => 'Hampers created successfully',
            'data' => $hampers
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'id_produk' => 'required',
            'id_packaging' => 'required',
            'limit_harian' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $hampers->update($data);

        return response([
            'message' => 'Hampers updated successfully',
            'data' => $hampers
        ], 200);
    }

    public function destroy($id)
    {
        $hampers = Hampers::find($id);

        if (!$hampers) {
            return response(['message' => 'Hampers not found'], 404);
        }

        $hampers->delete();

        return response(['message' => 'Hampers deleted successfully'], 200);
    }
}