<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Withdraw;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    public function showAll()
    {
        $withdraws = Withdraw::all();

        return response([
            'message' => 'All Withdraws Retrieved',
            'data' => $withdraws
        ], 200);
    }

    public function showById($id)
    {
        $withdraw = Withdraw::find($id);

        if (!$withdraw) {
            return response(['message' => 'Withdraw not found'], 404);
        }

        return response([
            'message' => 'Show Withdraw Successfully',
            'data' => $withdraw
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
           'id_user' => 'required',
           'jumlah_withdraw' => 'required',
           'status_withdraw' => 'required',
           'tanggal' => 'required',
           'nama_bank' => 'required',
           'no_rek' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $withdraw = Withdraw::create($data);

        return response([
            'message' => 'Withdraw created successfully',
            'data' => $withdraw
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $withdraw = Withdraw::find($id);

        if (!$withdraw) {
            return response(['message' => 'Withdraw not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $withdraw->update($data);

        return response([
            'message' => 'Withdraw updated successfully',
            'data' => $withdraw
        ], 200);
    }

    public function destroy($id)
    {
        $withdraw = Withdraw::find($id);

        if (!$withdraw) {
            return response(['message' => 'Withdraw not found'], 404);
        }

        $withdraw->delete();

        return response(['message' => 'Withdraw deleted successfully'], 200);
    }

    public function withdraw(Request $request)
    {
        return response(["message"]);
        $data = $request->all();
        $id = Auth::user()->id_user;
        $validate = Validator::make($data, [
            "jumlah" => "required",
            "nama_bank" => "required",
            "no_rek" => "required",
        ]);

        

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $data['tanggal'] = now();
        $data['status'] = "menunggu konfirmasi";
        $data['id_user'] = $id;
        $withdraw = Withdraw::create($data);

        return response([
            'message' => 'Wallet updated successfully',
            'data' => $withdraw
        ], 200);
    }
}
