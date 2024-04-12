<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackagingController extends Controller
{
    public function showAll()
    {
        $packagings = Packaging::all();

        return response([
            'message' => 'All Packaging Retrieved',
            'data' => $packagings
        ], 200);
    }

    public function showById($id)
    {
        $packaging = Packaging::find($id);

        if (!$packaging) {
            return response(['message' => 'Packaging not found'], 404);
        }

        return response([
            'message' => 'Show Packaging Successfully',
            'data' => $packaging
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_packaging' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $packaging = Packaging::create($data);

        return response([
            'message' => 'Packaging created successfully',
            'data' => $packaging
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $packaging = Packaging::find($id);

        if (!$packaging) {
            return response(['message' => 'Packaging not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_packaging' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $packaging->update($data);

        return response([
            'message' => 'Packaging updated successfully',
            'data' => $packaging
        ], 200);
    }

    public function destroy($id)
    {
        $packaging = Packaging::find($id);

        if (!$packaging) {
            return response(['message' => 'Packaging not found'], 404);
        }

        $packaging->delete();

        return response(['message' => 'Packaging deleted successfully'], 200);
    }
}
