<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Penitip;
use App\Models\Produk;
use App\Models\ProdukTitipan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenitipController extends Controller
{
    public function showAll()
    {
        $penitips = Penitip::all();

        foreach ($penitips as $penitip) {
            $products = DB::table('produk_titipan as PT')
                ->select('P.*', 'PR.*', 'RS.*')
                ->join('penitip as P', 'P.id_penitip', '=', 'PT.id_penitip')
                ->join('produk as PR', 'PT.id_produk', '=', 'PR.id_produk')
                ->join('ready_stok as RS', 'PR.id_stok_produk', '=', 'RS.id_stok_produk')
                ->where('PT.ID_PENITIP', '=', $penitip->id_penitip)
                ->get();

            $penitip->produk = $products;
        }

        return response([
            'message' => 'All Penitip Retrieved',
            'data' => $penitips
        ], 200);
    }

    public function showById($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        return response([
            'message' => 'Show Penitip Successfully',
            'data' => $penitip
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_penitip' => 'required',
            'no_telp_penitip' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $penitip = Penitip::create($data);

        return response([
            'message' => 'Penitip created successfully',
            'data' => $penitip
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_penitip' => 'required',
            'no_telp_penitip' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $penitip->update($data);

        return response([
            'message' => 'Penitip updated successfully',
            'data' => $penitip
        ], 200);
    }

    public function destroy($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response(['message' => 'Penitip not found'], 404);
        }

        $penitip->delete();

        return response(['message' => 'Penitip deleted successfully'], 200);
    }
}
