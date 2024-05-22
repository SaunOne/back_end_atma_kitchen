<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Alamat;

use App\Models\Hampers;
use App\Models\LimitOrder;
use App\Models\ReadyStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function showAll()
    {
        $transaksis = Transaksi::select('transaksi.*', 'users.name_lengkap')
            ->join('users', 'users.id_user', 'transaksi.id_user')->get();
        foreach ($transaksis as $transaksi) {
            $detail_transaksis = DetailTransaksi::where('id_transaksi', $transaksi->id_transaksi)->get();
            $transaksi->detail_transaksi = $detail_transaksis;
            $transaksi->alamat = Alamat::where('id_alamat', $transaksi->id_alamat)->first();
            foreach ($detail_transaksis as $detail_transaksi) {
                $products = Produk::where('id_produk', $detail_transaksi->id_produk)->first();
                $detail_transaksi->produk = $products;
            }
        }

        return response([
            'message' => 'All Transaksis Retrieved',
            'data' => $transaksis
        ], 200);
    }

    public function showByUser()
    {
        $id_user =  Auth::user()->id_user;
        $transaksis = Transaksi::select('transaksi.*', 'users.name_lengkap')
            ->join('users', 'users.id_user', 'transaksi.id_user')
            ->where('transaksi.id_user', $id_user)->get();
        foreach ($transaksis as $transaksi) {
            $detail_transaksis = DetailTransaksi::where('id_transaksi', $transaksi->id_transaksi)->get();
            $transaksi->detail_transaksi = $detail_transaksis;
            $transaksi->alamat = Alamat::where('id_alamat', $transaksi->id_alamat)->first();
            foreach ($detail_transaksis as $detail_transaksi) {
                $products = Produk::where('id_produk', $detail_transaksi->id_produk)->first();
                $detail_transaksi->produk = $products;
            }
        }

        return response([
            'message' => 'All Transaksis Retrieved',
            'data' => $transaksis,
        ], 200);
    }

    public function showById($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        return response([
            'message' => 'Show Transaksi Successfully',
            'data' => $transaksi
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        //cek validasi data yang diperlukan 
        $validate = Validator::make($data, [
            'detail_transaksi' => 'required',
            'id_user' => 'required',
            'jenis_pesanan' => 'required',

        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        };

        $idProdukList = array_map(function ($detail) {
            return $detail['id_produk'];
        }, $data['detail_transaksi']);

        //cek ready stoknya
        if ($data['jenis_transaksi'] == 'ready stok') {
            $data['tanggal_pesan'] = now();

            $dt = Produk::select()
                ->join('ready_stok', 'produk.id_stok_produk', 'ready_stok.id_stok_produk')
                ->whereIn('produk.id_produk', $idProdukList)
                ->get();


            for ($i = 0; $i < $dt->count(); $i++) {
                if ($data['detail_transaksi'][$i]['jumlah_produk'] > $dt[$i]['jumlah_stok']) {
                    $dt[$i]['message'] = $dt[$i]['nama_produk'] . " tersisa " . $dt[$i]['jumlah_stok'] . " " . $dt[$i]['satuan'];
                    $kekuranganStok[] = $dt[$i];
                }
            }

            if (!$kekuranganStok->isEmpty()) {
                return response([
                    'message' => $kekuranganStok
                ], 400);
            }
        }

        //cek limit transaksi
        if ($data['jenis_transaksi'] == 'pre order') {
            $validate = Validator::make($data, [
                'tanggal_pesan' => 'required',
            ]);

            if ($validate->fails()) {
                return response(['message' => $validate->errors()->first()], 400);
            };

            //cek stok produk titipan
            $data = $request->all();

            $idProdukList = array_map(function ($detail) {
                return $detail['id_produk'];
            }, $data['detail_transaksi']);

            $produkData = Produk::select('produk.*', 'lo.*', 'rs.*')
                ->join('produk_utama as pu', 'produk.id_produk', '=', 'pu.id_produk')
                ->join('limit_order as lo', 'produk.id_produk', '=', 'lo.id_produk')
                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                ->whereIn('produk.id_produk', $idProdukList)
                ->where('lo.tanggal', '=', $data['tanggal_pengambilan'])
                ->get();

            for ($i = 0; $i < count($produkData); $i++) {
                if ($produkData[$i]['jumlah_sisa'] < $data['detail_transaksi'][$i]['jumlah_produk']) {
                    $produkData[$i]['message'] = $produkData[$i]['nama_produk'] . " tersisa " . $produkData[$i]['jumlah_sisa'] . " " . $produkData[$i]['satuan'];
                    $kelimit[] = $produkData[$i];
                }
            }

            return response([
                "data" => $kelimit
            ], 400);
        }


        //create transaksi terlebih dahulu
        $transaksi = Transaksi::create($data);

        //setelah transaksi membvuat detailnya


    }


    public function test(Request $request)
    {
        $data = $request->all();

        $idProdukList = array_map(function ($detail) {
            return $detail['id_produk'];
        }, $data['detail_transaksi']);

        
    }

    public function cekStok(Request $request)
    {

        $data = $request->all();

        $idProdukList = array_map(function ($detail) {
            return $detail['id_produk'];
        }, $data['detail_transaksi']);

        //fetch dulu biar tau jenis produk apa itu
        $produkData = Produk::select('produk.*')
            ->whereIn('produk.id_produk', $idProdukList)
            ->get();

        // return response(["id_produk " => $produkData['id_produk']]);

        //untuk menampung produk yang kekurangan stok / limit harian
        $listEror = [];

        //kasus pre order
        if ($data['jenis_pesanan'] == "pre order") {
            //kemudain lakukan perulangan untuk pengecekannya
            for ($i = 0; $i < count($produkData); $i++) {
                if ($produkData[$i]['jenis_produk'] == 'Utama') {
                    $produkUtama = Produk::select('produk.*', 'lo.*')
                        ->join('produk_utama as pu', 'produk.id_produk', '=', 'pu.id_produk')
                        ->join('limit_order as lo', 'produk.id_produk', '=', 'lo.id_produk')
                        ->where('produk.id_produk', $produkData[$i]['id_produk'])
                        ->where('lo.tanggal', $data['tanggal_pengambilan'])
                        ->first();

                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }

                    if ($produkUtama['jumlah_sisa'] < $jumlahProduk) {
                        $produkUtama["message"] = $produkUtama['nama_produk'] . " tersisa " . $produkUtama['jumlah_sisa'] . " " . $produkUtama['satuan'];
                        $listEror[] = $produkUtama;
                    }
                } else if ($produkData[$i]['jenis_produk'] == 'Titipan') {
                    //mencari produk titipannya dulu agar yang di cek adalah ready stoknya saat terjadi pesanan pre order namun produk titipan
                    $produkTitipan = Produk::select('produk.*', 'rs.*')
                        ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
                        ->where('produk.id_produk', $produkData[$i]['id_produk'])
                        ->first();

                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }

                    if ($produkTitipan['jumlah_sisa'] < $jumlahProduk) {
                        $produkTitipan["message"] = $produkTitipan['nama_produk'] . " tersisa " . $produkTitipan['jumlah_sisa'] . " " . $produkTitipan['satuan'];
                        $listEror[] = $produkTitipan;
                    }
                } else if ($produkData[$i]['jenis_produk'] == 'Hampers') {
                    // cari pecahan produk hampersnya
                    $listProdukHampers = DB::table('detail_hampers as dh')
                        ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                        ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                        ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                        ->join('limit_order as lo', 'p.id_produk', '=', 'lo.id_produk')
                        ->select('p.*', 'lo.*', 'dh.*')
                        ->where('h.id_produk', $produkData[$i]['id_produk'])
                        ->where('tanggal', $data['tanggal_pengambilan'])
                        ->get();

                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }
                    foreach ($listProdukHampers as $produkHampers) {
                        $jumlahProduk *= $produkHampers->jumlah_produk;

                        if ($produkHampers->jumlah_sisa < $jumlahProduk) {
                            //mencari data hampers yang kelebihan dll
                            $temp = Produk::select('produk.*', 'rs.*')
                                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
                                ->where('produk.id_produk', $produkData[$i]->id_produk)
                                ->first();


                            $minJumlahSisa = DB::table('detail_hampers as dt')
                                ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
                                ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
                                ->join('limit_order as lo', 'lo.id_produk', '=', 'pu.id_produk')
                                ->where('dt.id_hampers', $temp['id_produk'])
                                ->where('lo.tanggal', $data['tanggal_pengambilan'])
                                ->min('lo.jumlah_sisa');

                            $temp["message"] = $temp['nama_produk'] . " tersisa " . $minJumlahSisa . " " . $temp['satuan'];
                            $listEror[] = $temp;
                            break;
                        }
                    }
                }
            }
            if(!($listEror == [])){
                return response([
                    "message" => $listEror
                ],400);
            }
            
        } else if ($data['jenis_pesanan'] == "ready stok") {
            //kasus ready stok

            //pisah agar ketika ada seesuatu yang khusus dalam pembelian setipa produk bisa aman
            for ($i = 0; $i < count($produkData); $i++) {
                if ($produkData[$i]['jenis_produk'] == "Utama") {
                    $produkUtama = Produk::select('produk.*', 'rs.*')
                        ->join('produk_utama as pu', 'produk.id_produk', '=', 'pu.id_produk')
                        ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
                        ->where('produk.id_produk', $produkData[$i]['id_produk'])
                        ->first();

                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }
                    // return response([
                    //     "dibeli" => $jumlahProduk,
                    //     "stok" => $produkUtama["jumlah_stok"]
                    // ]);
                    if ($produkUtama['jumlah_stok'] < $jumlahProduk) {
                        $produkUtama["message"] = $produkUtama['nama_produk'] . " tersisa " . $produkUtama['jumlah_stok'] . " " . $produkUtama['satuan'];
                        $listEror[] = $produkUtama;
                    }
                } else if ($produkData[$i]['jenis_produk'] == 'Titipan') {
                    $produkTitipan = Produk::select('produk.*', 'rs.*')
                        ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
                        ->where('produk.id_produk', $produkData[$i]['id_produk'])
                        ->first();

                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }

                    if ($produkTitipan['jumlah_stok'] < $jumlahProduk) {
                        $produkTitipan["message"] = $produkTitipan['nama_produk'] . " tersisa " . $produkTitipan['jumlah_stok'] . " " . $produkTitipan['satuan'];
                        $listEror[] = $produkTitipan;
                    }
                } else if ($produkData[$i]['jenis_produk'] == 'Hampers') {

                    //cari pecahan produk hampersnya
                    $listProdukHampers = DB::table('detail_hampers as dh')
                        ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                        ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                        ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                        ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                        ->select('p.*', 'rs.*', 'dh.*')
                        ->where('h.id_produk', $produkData[$i]['id_produk'])
                        ->get();
                    // 
                    $jumlahProduk = null;
                    foreach ($data['detail_transaksi'] as $detail) {
                        if ($detail['id_produk'] == $produkData[$i]['id_produk']) {
                            $jumlahProduk = $detail['jumlah_produk'];
                            break;
                        }
                    }

                    foreach ($listProdukHampers as $produkHampers) {
                        $jumlahProduk *= $produkHampers->jumlah_produk;

                        if ($produkHampers->jumlah_stok < $jumlahProduk) {
                            //mencari data hampers yang kelebihan dll
                            $temp = Produk::select('produk.*', 'rs.*')
                                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'produk.id_stok_produk')
                                ->where('produk.id_produk', $produkData[$i]->id_produk)
                                ->first();

                            $minJumlahStok = DB::table('detail_hampers as dt')
                                ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
                                ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
                                ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                                ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                                ->where('dt.id_hampers', $temp['id_produk'])
                                ->min('rs.jumlah_stok');

                            $temp["message"] = $temp['nama_produk'] . " tersisa " . $minJumlahStok . " " . $temp['satuan'];
                            $listEror[] = $temp;
                            break;
                        }
                    }
                }
            }
            if(!($listEror == [])){
                return response([
                    "message" => $listEror
                ],400);
            }
        } else {
            return response([
                "message" => "jenis pesanan tidak valid!!"
            ], 400);
        }

        return response([
            "meesage" => "Stok Produk Tersedia Semua"
        ],200);
    }

    public function chekOut(Request $request){

        $data = $request->all();

        $data['id_user'] = Auth::user()->id_user;

        $validate = Validator::make($data, [
           "jenis_pesanan" => "required",
           "detail_transaksi" => "required",
           "id_packgaging" => "required",
           "point_terpakai" => "required",
           "jenis_pengiriman" => "required"
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

       if($data['jenis_pesanan'] == "ready stok"){
            $data['tanggal_pengambilan'] = now();
       }

       if($data['jenis_pengiriman'] == "Atma Delivery"){
            $data['status_transaksi'] = "Menunggu Biaya Pengiriman";
       } else {
            $data['sataus_transaksi'] = "Menunggu Pembayaran";
       }
       

       $transaksi = Transaksi::create($data);

       foreach($data['detail_transaksi'] as $dt){
            DetailTransaksi::create($dt);
       }


       return response([
            "message" => "successfully create transaksi",
            "data" => $transaksi
       ],200);
    }

    public function konfirmasiPembayaran(Request $request){
        $data = $request->all();

        $transaksi = Transaksi::find($data['id_transaksi']);

        if (!$transaksi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        $data['status_transaksai'] = 'Sudah Dibayar';
        $transaksi->update([
            $data 
        ]);
        

        return response([
            "message" => "Transaksi Di Update Sudah bayar",
            "data" => $transaksi
        ],200);
    }

    public function konfirmasiPesanan(Request $request){
         $data = $request->all();

        $transaksi = Transaksi::find($data['id_transaksi']);

        if (!$transaksi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        $transaksi->update([
            $data  
        ]);

        if($data['status_transaksi'] == 'Ditolak'){
            //balikin stoknya
        }

        return response([
            "message" => "Transaksi Di Update Sudah bayar",
            "data" => $transaksi
        ],200);

    }

    public function hitungSisaHampers()
    {

    }

    public function hitungLimitHampers()
    {

    }

    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            // add validation rules for your fields
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi->update($data);

        return response([
            'message' => 'Transaksi updated successfully',
            'data' => $transaksi
        ], 200);
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Transaksi not found'], 404);
        }

        $transaksi->delete();

        return response(['message' => 'Transaksi deleted successfully'], 200);
    }
}
