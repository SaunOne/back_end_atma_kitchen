<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranLainLain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PengeluaranLainLainController extends Controller
{
    public function showAll()
    {
        $pengeluarans = PengeluaranLainLain::select('pengeluaran_lain_lain.*')
        ->orderBy('pengeluaran_lain_lain.tanggal', 'desc')
        ->get();

        return response([
            'message' => 'All Pengeluaran Lain-lain Retrieved',
            'data' => $pengeluarans
        ], 200);
    }

    public function showById($id)
    {
        $pengeluaran = PengeluaranLainLain::find($id);

        if (!$pengeluaran) {
            return response(['message' => 'Pengeluaran Lain-lain not found'], 404);
        }

        return response([
            'message' => 'Show Pengeluaran Lain-lain Successfully',
            'data' => $pengeluaran
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_pengeluaran' => 'required',
            'jumlah_pengeluaran' => 'required'
        ]);

        $data['tanggal'] = Carbon::now()->format('Y-m-d');

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $pengeluaran = PengeluaranLainLain::create($data);

        return response([
            'message' => 'Pengeluaran Lain-lain created successfully',
            'data' => $pengeluaran
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $pengeluaran = PengeluaranLainLain::find($id);

        if (!$pengeluaran) {
            return response(['message' => 'Pengeluaran Lain-lain not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_pengeluaran' => 'required',
            'tanggal' => 'required',
            'jumlah_pengeluaran' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $pengeluaran->update($data);

        return response([
            'message' => 'Pengeluaran Lain-lain updated successfully',
            'data' => $pengeluaran
        ], 200);
    }

    public function destroy($id)
    {
        $pengeluaran = PengeluaranLainLain::find($id);

        if (!$pengeluaran) {
            return response(['message' => 'Pengeluaran Lain-lain not found'], 404);
        }

        $pengeluaran->delete();

        return response(['message' => 'Pengeluaran Lain-lain deleted successfully'], 200);
    }
}