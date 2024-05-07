<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailHampers;
use App\Models\Produk;
use App\Models\ReadyStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\api\ProdukTitipanController;
use App\Http\Controllers\api\ProdukUtamaController;
use App\Http\Controllers\api\HampersController;
use App\Http\Controllers\api\ReadyStokController;
use App\Http\Controllers\api\DetailHampersController;
use App\Models\Hampers;
use PhpParser\Node\Expr\CallLike;

class ProdukController extends Controller
{
    public function showAll()
    {
        $produks = Produk::all();

        foreach ($produks as $produk) {
            if ($produk->jenis_produk == "Titipan") {
                
            } else if ($produk->jenis_produk == "Hampers") {
                $products = DB::table('detail_hampers as dh')
                    ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                    ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                    ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                    ->select('p.*')
                    ->where('h.id_produk', '=', $produk->id_produk)
                    ->get();
                    $produk->produk = $products;
            } else if ($produk->jenis_produk == "Utama") {
                
            }
        }

        return response([
            'message' => 'All Produk Retrieved',
            'data' => $produks
        ], 200);
    }

    public function showById($id)
    {

        $produk = Produk::where($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            'message' => 'Show Produk Successfully',
            'data' => $produk
        ], 200);
    }

    public function searchProduk($search)
    {

        $result = Produk::where('nama_produk', 'like', '%' . $search . '%')
            ->orWhere('nama_produk', 'like', '%' . $search . '%')
            ->orWhere('jenis_produk', 'like', '%' . $search . '%')
            ->orWhere('harga', 'like', '%' . $search . '%')
            ->orWhere('deskripsi', 'like', '%' . $search . '%')
            ->get();

        if ($result === []) {
            return response([
                "message" => "Produk not found",
                "result" => $search,
            ]);
        }

        return response([
            "message" => "Show Produk Successfully",
            "data" => $result
        ]);
    }



    public function store(Request $request)
    {
        $data = $request->all();

        if (!isset($data['id_produk'])) {
            if ($data['jenis_produk'] == 'produk utama') {
                $validate = Validator::make($data, [
                    'id_packaging' => 'required',
                    'katagorie_produk' => 'required',
                ]);
                if ($validate->fails()) {
                    return response(['message' => $validate->errors()->first()], 400);
                }
            } else if ($data['jenis_produk'] == 'produk titipan') {
                $validate = Validator::make($data, [
                    'id_penitip' => 'required',
                    'jumlah_produk_dititip' => 'required',
                ]);
                if ($validate->fails()) {
                    return response(['message' => $validate->errors()->first()], 400);
                }
            } else if ($data['jenis_produk'] == 'hampers') {
                $validate = Validator::make($data, [
                    'id_packaging' => 'required',
                    'limit_harian' => 'required',
                    'detail_hampers' => 'required'
                ]);
                if ($validate->fails()) {
                    return response(['message' => $validate->errors()->first()], 400);
                }
            }
        }
        $validate = Validator::make($data, [
            'nama_produk' => 'required',
            'harga' => 'required',
            'quantity' => 'required',
            'deskripsi' => 'required',
            'jenis_produk' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        if ($data['jenis_produk'] === 'produk titipan' && isset($data['id_produk'])) {
            $data['id_ready_stok'] = Produk::where('id_produk', $data['id_produk'])->value('id_ready_stok');
            $data['jumlah_stok'] = $data['jumlah_produk_dititip'];
        }

        $readyStok = ReadyStok::updateOrCreate(
            ['id_ready_stok' => $data['id_ready_stok'] ?? null],
            ['jumlah_stok' => DB::raw('jumlah_stok + ' . ($data['jumlah_stok'] ?? 0))]
        );

        if (!isset($data['id_ready_stok'])) {
            $readyStok['satuan'] = $data['satuan'];
            $readyStok['jumlah_stok'] = $data['jumlah_stok'];
            $readyStok->save();
            $data['id_ready_stok'] = $readyStok['id_ready_stok'];
        }

        $produk = Produk::updateOrCreate(
            ['id_produk' => $data['id_produk'] ?? null],
            $data
        );

        $data['id_produk'] = $produk['id_produk'];

        switch ($data['jenis_produk']) {
            case 'produk utama':
                app(ProdukUtamaController::class)->store(new Request($data));
                break;
            case 'hampers':
                $data['DetailHampers']['id_hampers'] = $produk['id_produk'];
                app(HampersController::class)->store(new Request($data));
                $this->handleDetailHampers($data);
                break;
            case 'produk titipan':
                app(ProdukTitipanController::class)->store(new Request($data));
                break;
        }

        return response(['message' => 'Produk created successfully'], 200);
    }

    protected function handleDetailHampers($data)
    {
        foreach ($data['detail_hampers'] as $dH) {
            app(DetailHampersController::class)->store(new Request(array_merge($dH, ["id_hampers" => $data["id_produk"]])));
        }
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_produk' => 'required',
            'harga' => 'required',
            'quantity' => 'required',
            'deskripsi' => 'required',
            'jenis_produk' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produk->update($data);

        return response([
            'message' => 'Produk updated successfully',
            'data' => $produk
        ], 200);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $produk->delete();

        return response(['message' => 'Produk deleted successfully'], 200);
    }
}
