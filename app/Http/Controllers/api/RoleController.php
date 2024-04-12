<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function showAll()
    {
        $roles = Role::all();

        return response([
            'message' => 'All Roles Retrieved',
            'data' => $roles
        ], 200);
    }

    public function showById($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response(['message' => 'Role not found'], 404);
        }

        return response([
            'message' => 'Show Role Successfully',
            'data' => $role
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_role' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $role = Role::create($data);

        return response([
            'message' => 'Role created successfully',
            'data' => $role
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response(['message' => 'Role not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_role' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $role->update($data);

        return response([
            'message' => 'Role updated successfully',
            'data' => $role
        ], 200);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response(['message' => 'Role not found'], 404);
        }

        $role->delete();

        return response(['message' => 'Role deleted successfully'], 200);
    }
}
