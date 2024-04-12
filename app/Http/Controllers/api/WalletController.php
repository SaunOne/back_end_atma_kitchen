<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function showAll()
    {
        $wallets = Wallet::all();

        return response([
            'message' => 'All Wallets Retrieved',
            'data' => $wallets
        ], 200);
    }

    public function showById($id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response(['message' => 'Wallet not found'], 404);
        }

        return response([
            'message' => 'Show Wallet Successfully',
            'data' => $wallet
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'jumlah_saldo' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $wallet = Wallet::create($data);

        return response([
            'message' => 'Wallet created successfully',
            'data' => $wallet
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response(['message' => 'Wallet not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $wallet->update($data);

        return response([
            'message' => 'Wallet updated successfully',
            'data' => $wallet
        ], 200);
    }

    public function destroy($id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response(['message' => 'Wallet not found'], 404);
        }

        $wallet->delete();

        return response(['message' => 'Wallet deleted successfully'], 200);
    }
}
