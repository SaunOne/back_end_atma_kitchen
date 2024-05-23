<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailHampers;
use App\Models\Produk;
use App\Models\ProdukUtama;
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
use App\Models\ProdukTitipan;
use PhpParser\Node\Expr\CallLike;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function showAll()
    {

        //init buat list prdouknya
        $listProduk = [];

        $produkUtama = Produk::select(
            'produk.*',
            'lo.*',
            'rs.*'
        )
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'produk.id_produk')
            ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
            ->where('lo.tanggal', '2024-05-19')
            ->get();
        
        $listProduk = $produkUtama;

        //cari produk titipan
        $produkTitipan = Produk::select('produk.*', 'rs.*')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
            ->get();

        $listProduk = $produkTitipan->merge($listProduk);

        $produkHampers = Produk::select()
            ->where('jenis_produk', 'Hampers')
            ->get();

        foreach ($produkHampers as $produk) {
            $products = DB::table('detail_hampers as dh')
                ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                ->select('p.*', 'rs.*')
                ->where('h.id_produk', '=', $produk->id_produk)
                ->get();
            
            $produk->produk = $products;
            $produk->jumlah_sisa = DB::table('detail_hampers as dt')
            ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
            ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
            ->where('dt.id_hampers', $produk['id_produk'])
            ->where('lo.tanggal', '2024-5-19') //nanti inget ganti ke now()
            ->min('lo.jumlah_sisa');

            $produk->jumlah_stok = DB::table('detail_hampers as dt')
            ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
            ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
            ->where('dt.id_hampers', $produk['id_produk'])
            ->min('rs.jumlah_stok');

            $listProdukHampers[] = $produk;
        }
        $listProduk = $listProduk->merge($listProdukHampers);

        return response([
            'message' => 'All Produk Retrieved',
            'data' => $listProduk
        ], 200);
    }

    public function showAllByTanggal($tanggal)
    {

        //init buat list prdouknya
        $listProduk = [];

        $produkUtama = Produk::select(
            'produk.*',
            'lo.*',
            'rs.*'
        )
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'produk.id_produk')
            ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
            ->where('lo.tanggal', '2024-05-19')
            ->get();

        $listProduk = $produkUtama;

        //cari produk titipan
        $produkTitipan = Produk::select('produk.*', 'rs.*')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
            ->get();
 
        $listProduk = $produkTitipan->merge($listProduk);

        $produkHampers = Produk::select()
            ->where('jenis_produk', 'Hampers')
            ->get();

        foreach ($produkHampers as $produk) {
            $products = DB::table('detail_hampers as dh')
                ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                ->select('p.*', 'rs.*')
                ->where('h.id_produk', '=', $produk->id_produk)
                ->get();
            
            $produk->produk = $products;
            $produk->jumlah_sisa = DB::table('detail_hampers as dt')
            ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
            ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
            ->where('dt.id_hampers', $produk['id_produk'])
            ->where('lo.tanggal', '2024-5-19') //nanti inget ganti ke now()
            ->min('lo.jumlah_sisa');

            $produk->jumlah_stok = DB::table('detail_hampers as dt')
            ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
            ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
            ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
            ->where('dt.id_hampers', $produk['id_produk'])
            ->min('rs.jumlah_stok');

            $listProdukHampers[] = $produk;
        }
        $listProduk = $listProduk->merge($listProdukHampers);

        return response([
            'message' => 'All Produk Retrieved',
            'data' => $listProduk
        ], 200);
    }



    public function showById($id)
    {

        $produk = Produk::where('id_produk', $id)->first();

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            'message' => 'Show Produk Successfully',
            'data' => $produk
        ], 200);
    }

    // public function showByIdAll($id)
    // {

    //     $produk = Produk::find($id)->first();

    //     if (!$produk) {
    //         return response(['message' => 'Produk not found'], 404);
    //     }

    //     return response([
    //         'message' => 'Show Produk Successfully',
    //         'data' => $produk
    //     ], 200);
    // }

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
        $data['limit_harian'] = 5;

        if ($request->hasFile('image_produk')) {
            $uploadFolder = 'images';
            $image = $request->file('image_produk');

            if ($data['image_produk']) {
                Storage::disk('public')->delete($data['image_produk']);
            }
            // Generate nama file acak dengan 12 karakter
            $randomFileName = Str::random(12);
            // Dapatkan ekstensi file asli
            $extension = $image->getClientOriginalExtension();
            // Gabungkan nama file acak dengan ekstensi
            $fileNameToStore = $randomFileName . '.' . $extension;
            // Simpan gambar
            $image_uploaded_path = $image->storeAs($uploadFolder, $fileNameToStore, 'public');
            // Mendapatkan nama file yang diunggah
            $uploadedImageResponse = basename($image_uploaded_path);
            // Set data foto profile baru
            $data['image_produk'] = 'images/' . $uploadedImageResponse;
        }

        switch ($data['jenis_produk']) {

                //PEMBEDAN ANTARA LAMA DAN BARU ITU ID_STOK_PRODUK
            case 'Utama':

                $validate = Validator::make($data, [
                    'id_packaging' => 'required',
                    'jenis_produk' => 'required',
                ]);

                //kalo dai gak punya id_stok_produk berarti produk utama baru
                if (!isset($data['id_stok_produk'])) {

                    $validate = Validator::make($data, [
                        // 'id_stok_produk' => 'required',
                        'satuan' => 'required',
                        'nama_produk_stok' => 'required',
                    ]);

                    if ($validate->fails()) {
                        return response(
                            ["Message" => $validate->errors()->first(), 400]
                        );
                    }
                    //jumlah stoknya kita 0 dulu
                    $data['jumlah_stok'] = 0;
                    $readyStok = ReadyStok::create($data);

                    $data['id_stok_produk'] = $readyStok->id_stok_produk;
                    $produk = Produk::create($data);
                    $data['id_produk'] = $produk['id_produk'];
                    $produk_utama = ProdukUtama::create($data);
                    $produk_utama->id_produk = $data['id_produk'];
                    $produk->save();
                    return response([
                        "message" => "Berhasil Add Produk Utama",
                        "data produk" =>   $produk,
                        "data produk_utama " => $produk_utama,
                        "raady_stok" => $readyStok,
                        "data" => $data
                    ]);
                } else {
                    $readyStok = ReadyStok::where('id_stok_produk', $data['id_stok_produk']);
                    $produk = Produk::create($data);
                    $data['id_produk'] = $produk['id_produk'];
                    $produk_utama = ProdukUtama::create($data);
                    $produk_utama->id_produk = $data['id_produk'];
                    $produk->save();
                    return response([
                        "message" => "Berhasil Add Produk Utama",
                        "data produk" =>   $produk,
                        "data produk_utama " => $produk_utama,
                        "raady_stok" => $readyStok,
                        "data" => $data
                    ]);
                }
                break;
            case "Titipan":
                if (!isset($data['id_produk'])) {
                    $validate = Validator::make($data, [
                        // 'id_stok_produk' => 'required',
                        'satuan' => 'required',
                    ]);

                    if ($validate->fails()) {
                        return response(
                            ["Message" => $validate->errors()->first(), 400]
                        );
                    }
                    //jumlah stoknya kita 0 dulu
                    $data['jumlah_stok'] = 0;
                    $data['nama_produk_stok'] = $data['nama_produk'];
                    $readyStok = ReadyStok::create($data);
                    $data['id_stok_produk'] = $readyStok->id_stok_produk;
                    $readyStok->jumlah_stok = $data['jumlah_produk_dititip'];
                    $readyStok->save();
                    $produk = Produk::create($data);
                    $data['id_produk'] = $produk['id_produk'];

                    $produk_titipan = ProdukTitipan::create($data);

                    return response([
                        "message" => "Berhasil Add Produk Titipan",
                        "data produk" =>   $produk,
                        "data produk_titipan " => $produk_titipan,
                        "raady_stok" => $readyStok,
                        "data" => $data
                    ]);
                } else {
                    //kalo tiitpan lama  ada id produk
                    $validate = Validator::make($data, [
                        'id_produk' => 'required',
                    ]);

                    $produk = Produk::where('id_produk', $data['id_produk'])->first();
                    $temp = ProdukTitipan::select('id_penitip')->where('id_produk', $data['id_produk'])->first();
                    $data['id_penitip'] = $temp->id_penitip;
                    $data['id_stok_produk'] = $produk->id_stok_produk;

                    $readyStok = ReadyStok::where('id_stok_produk', $data['id_stok_produk'])->first();
                    $readyStok->jumlah_stok += $data['jumlah_produk_dititip'];
                    $readyStok->save();
                    $data['tanggal'] = now();
                    $produk_titipan = ProdukTitipan::create($data);
                    $produk_titipan->id_penitip = $data['id_penitip'];

                    return response([
                        "message" => "Berhasil Add Produk Titipan",
                        "data produk" =>   $produk,
                        "data produk_titipan " => $produk_titipan,
                        "raady_stok" => $readyStok,
                        "data" => $data
                    ]);
                }

                break;
            case "Hampers":

                $validate = Validator::make($data, [
                    // 'id_stok_produk' => 'required',
                    'satuan' => 'required',
                ]);
                $readyStok = ReadyStok::create($data);
                $data['id_stok_produk'] = $readyStok->id_stok_produk;

                $produk = Produk::create($data);

                $data['id_produk'] = $produk->id_produk;

                $hampers = new Hampers;
                $hampers->id_packaging = $data['id_packaging'];
                $hampers->id_produk = $data['id_produk'];
                $hampers->limit_harian = 5;
                $hampers->save();

                foreach ($data['detail_hampers'] as $dh) {
                    $dh['id_hampers'] = $data['id_produk'];
                    DetailHampers::create($dh);
                }
                return response([
                    "message" => "Berhasil Add Produk Titipan",
                    "data produk" =>   $produk,
                    "data hampers " => $hampers,
                    "raady_stok" => $readyStok,
                    "data" => $data
                ]);

                break;
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
