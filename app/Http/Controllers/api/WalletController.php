<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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

    public function withdraw(Request $request)
    {
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

        $data['tanggal'] = now();
        $data['status'] = "menunggu konfirmasi";
        $data['id_user'] = $id;
        $withdraw = Withdraw::create($data);

        return response([
            'message' => 'Wallet updated successfully',
            'data' => $withdraw
        ], 200);
    }

    public function konfirmasiWithdraw(Request $request,$id)
    {
        $data = $request->all();
        $validate = Validator::make($data, [
            "status" => "required",
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }
        
        $withdraw = Withdraw::find($id);
        if (!$withdraw) {
            return response(['message' => 'Withdraw not found'], 404);
        }

       if($data['status'] == 'diterima'){
            $withdraw['status'] = 'success';
       } else if($data['status'] == 'ditolak'){
            $withdraw['status'] = 'ditolak';
            return response(["message" => "Withdraw ditolak karna no rek atau bank tidak valid"]);
       }
       
        $wallet = Wallet::find($withdraw->id_user);

        $wallet->jumlah_saldo -= $withdraw->jumlah;
        $wallet->save();
        $withdraw->save();
        return response([
            'message' => 'Wallet updated successfully',
            'data' => $withdraw
        ], 200);
    }

    public function showByUser(Request $request)
    {
        $data = $request->all();
        $id = Auth::user()->id_user;

        $withdraw = Withdraw::select()
                    ->where('id_user',$id)
                    ->get();

        return response([
            'message' => 'Wallet updated successfully',
            'data' => $withdraw
        ], 200);
    }
}
