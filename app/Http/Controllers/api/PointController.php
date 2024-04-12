<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{
    public function showAll()
    {
        $points = Point::all();

        return response([
            'message' => 'All Points Retrieved',
            'data' => $points
        ], 200);
    }

    public function showByUserId($userId)
    {
        $point = Point::where('id_user', $userId)->first();

        if (!$point) {
            return response(['message' => 'Point not found for the user'], 404);
        }

        return response([
            'message' => 'Show Point Successfully',
            'data' => $point
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'id_user' => 'required|exists:users,id',
            'jumlah_point' => 'required|numeric|min:0',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $point = Point::create($data);

        return response([
            'message' => 'Point created successfully',
            'data' => $point
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $point = Point::find($id);

        if (!$point) {
            return response(['message' => 'Point not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'jumlah_point' => 'required|numeric|min:0',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $point->update($data);

        return response([
            'message' => 'Point updated successfully',
            'data' => $point
        ], 200);
    }

    public function destroy($id)
    {
        $point = Point::find($id);

        if (!$point) {
            return response(['message' => 'Point not found'], 404);
        }

        $point->delete();

        return response(['message' => 'Point deleted successfully'], 200);
    }
}
