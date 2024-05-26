<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Alamat;
use App\Models\Point;
use App\Models\User;
use App\Models\Hampers;
use App\Models\LimitOrder;
use App\Models\ReadyStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengeluaranLainLain;

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
        if ($data['jenis_transaksi'] == 'ready stock') {
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
        if ($data['jenis_transaksi'] == 'pre-order') {
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
        if ($data['jenis_pesanan'] == "pre-order") {
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
                        $produkUtama->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkUtama->status = false;
                        $listEror[] = $produkUtama;
                    } else {
                        $data['detail_transaksi'][$i]['jumlah_sisa'] = $produkUtama['jumlah_sisa'];
                        $produkUtama->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkUtama->status = true;
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
                        $produkTitipan->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkTitipan->status = false;
                        $listEror[] = $produkTitipan;
                    } else {
                        $produkTitipan->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkTitipan->status = true;
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


                        if ($produkHampers->jumlah_sisa < $jumlahProduk) {
                            //mencari data hampers yang kelebihan dll
                            $temp["message"] = $temp['nama_produk'] . " tersisa " . $minJumlahSisa . " " . $temp['satuan'];
                            $listEror[] = $temp;
                            $temp->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                            $temp->status = false;
                            $data['detail_transaksi'][$i]['jumlah_sisa'] = $minJumlahSisa;
                            $data['detail_transaksi'][$i]['message'] =  $temp['message'];
                            break;
                        } else {
                            $temp->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                            $temp->status = true;
                            $listEror[] = $temp;
                        }
                    }
                }
            }
            if (!($listEror == [])) {
                return response([
                    "message" => "stok atau limit harian tidak memenuhi",
                    "detail_transakasi" => $listEror,
                    "status" => false,
                ], 400);
            }
        } else if ($data['jenis_pesanan'] == "ready stock") {
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
                    $jumlah_stok = ($produkUtama['jumlah_stok'] / $produkUtama['quantity']);

                    if ($jumlah_stok < $jumlahProduk) {
                        $produkUtama["message"] = $produkUtama['nama_produk'] . " tersisa " . $produkUtama['jumlah_stok'] . " " . $produkUtama['satuan'];
                        $produkUtama->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkUtama->jumlah_stok = $jumlah_stok;
                        $produkUtama->status = false;
                        $listEror[] = $produkUtama;
                    } else {
                        $produkUtama->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkUtama->status = true;
                        $produkUtama->jumlah_stok = $jumlah_stok;
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
                        $produkTitipan->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkTitipan->status = true;
                        $listEror[] = $produkTitipan;
                    } else {
                        $produkTitipan->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                        $produkTitipan->status = true;
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

                        $quantity = DB::table('detail_hampers as dt')
                            ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
                            ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
                            ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                            ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                            ->where('dt.id_hampers', $temp['id_produk'])
                            ->orderBy('rs.jumlah_stok', 'asc')
                            ->select('p.quantity')
                            ->first();
                        
                        $minJumlahStok = ($minJumlahStok / $quantity->quantity);
                        $temp->jumlah_stok = $minJumlahStok;
                        if ($minJumlahStok < $jumlahProduk) {
                            //mencari data hampers yang kelebihan dll
                            $temp["message"] = $temp['nama_produk'] . " tersisa " . $minJumlahStok . " " . $temp['satuan'];
                            $temp->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                            $temp->status = false;
                            $listEror[] = $temp;
                            break;
                        } else {
                            $temp->jumlah_produk = $data['detail_transaksi'][$i]['jumlah_produk'];
                            $temp->status = true;
                            $listEror[] = $temp;
                        }
                    }
                }
            }
            if (!($listEror == [])) {
                return response([
                    "message" => "stok atau limit harian tidak memenuhi",
                    "detail_transakasi" => $listEror,
                    "status" => false,
                ], 400);
            }
        } else {
            return response([
                "message" => "jenis pesanan tidak valid!!"
            ], 400);
        }

        return response([
            "meesage" => "Stok Produk Tersedia Semua",
            "data" => $data,
            "status" => true
        ], 200);
    }

    public function chekOut(Request $request)
    {

        $data = $request->all();

        $data['id_user'] = Auth::user()->id_user;

        $validate = Validator::make($data, [
            "jenis_pesanan" => "required",
            "detail_transaksi" => "required",
            //    "id_packgaging" => "required",
            "total_harga_transaksi" => "required",
            "point_terpakai" => "required",
            "jenis_pengiriman" => "required"
        ]);



        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        if ($data['jenis_pesanan'] == "ready stock") {
            $data['tanggal_pengambilan'] = now();
        }

        if ($data['jenis_pengiriman'] == "Atma Kitchen Delivery") {
            $data['status_transaksi'] = "menunggu biaya pengiriman";
        } else {
            $data['status_transaksi'] = "menunggu pembayaran";
        }


        $transaksi = Transaksi::create($data);

        //dapatkan jumlah yang point di dapat ketika transaksi berhasil

        $year = Carbon::parse($transaksi['no_pengambilan'])->format('y');
        $month = Carbon::parse($transaksi['no_pengambilan'])->format('m');


        if ($transaksi['total_harga_transaksi'] >= 1000000) {
            $sisa_uang = $transaksi['total_harga_transaksi'] - ($transaksi['total_harga_transaksi'] % 1000000);
            $point = ($sisa_uang / 1000000) * 200;
        } else if ($transaksi['total_harga_transaksi'] >= 500000) {
            $sisa_uang = $transaksi['total_harga_transaksi'] - ($transaksi['total_harga_transaksi'] % 500000);
            $point = ($sisa_uang / 500000) * 75;
        } else if ($transaksi['total_harga_transaksi'] >= 100000) {
            $sisa_uang = $transaksi['total_harga_transaksi'] - ($transaksi['total_harga_transaksi'] % 100000);
            $point = ($sisa_uang / 100000) * 15;
        } else {
            $sisa_uang = $transaksi['total_harga_transaksi'] - ($transaksi['total_harga_transaksi'] % 10000);
            $point = ($sisa_uang / 10000) * 1;
        }

        $user = User::select('tanggal_lahir')->where('id_user', $data['id_user'])->first();

        // Konversi tanggal lahir dari string menjadi objek Carbon
        $tanggal_lahir = Carbon::parse($user->tanggal_lahir)->format('m-d');

        // Ambil tanggal saat ini dan konversi ke format bulan dan tanggal
        $tanggal_sekarang = Carbon::now()->format('m-d');
        // Membandingkan bulan dan tanggal
        if ($tanggal_lahir == $tanggal_sekarang) {
            $point *= 2;
        }

        $id_transaksi = $transaksi['id_transaksi'];
        $transaksi->no_transaksi = "{$year}.{$month}.{$id_transaksi}";
        $transaksi->point_diperoleh = $point;
        $transaksi->save();

        foreach ($data['detail_transaksi'] as $dt) {
            $dt['id_transaksi'] = $transaksi->id_transaksi;
            DetailTransaksi::create($dt);

            $produk = Produk::select()->where('id_produk', $dt['id_produk'])->first();

            if ($data['jenis_pesanan'] == "ready stock") {



                if ($produk['jenis_produk'] == 'Utama') {
                    $ready_stok = ReadyStok::find($produk['id_stok_produk']);
                    $ready_stok['jumlah_stok'] -= $dt['jumlah_produk'];
                    $ready_stok->save();
                } else if ($produk['jenis_produk'] == 'Titipan') {
                    $ready_stok = ReadyStok::find($produk['id_stok_produk']);
                    $ready_stok['jumlah_stok'] -= $dt['jumlah_produk'];
                    $ready_stok->save();
                } else if ($produk['jenis_produk'] == 'Hampers') {
                    $hampers = DB::table('detail_hampers as dt')
                        ->join('hampers as h', 'h.id_produk', '=', 'dt.id_hampers')
                        ->join('produk_utama as pu', 'pu.id_produk', '=', 'dt.id_produk')
                        ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                        ->where('dt.id_hampers', $produk['id_produk'])
                        ->get();

                    foreach ($hampers as $ph) {
                        $ready_stok = ReadyStok::find($ph->id_stok_produk);
                        $ready_stok['jumlah_stok'] -= (($dt['jumlah_produk'] * $ph->jumlah_produk) * $ph->quantity);
                        $ready_stok->save();
                    }
                }
            } else if ($data['pre-order']) {
                $limit_harian = LimitOrder::select()
                    ->where('id_produk', $dt['id_produk'])
                    ->where('tanggal', 'tanggal_pengambilan')
                    ->first();
            }
        }
        return response([
            "message" => "successfully create transaksi",
            "data" => $transaksi
        ], 200);
    }
    public function bayar(Request $request, $id)
    {

        $data = $request->all();

        $validate = Validator::make($data, [
            "bukti_pembayaran" => "required",
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'transaksi not found'], 404);
        }

        if (!$transaksi['status_transaksi'] == "menunggu pembayaran") {
            return response([
                "message" => "status_transaksi is not valid",
            ]);
        }

        $data['status_transaksi'] = 'sudah dibayar';
        $transaksi->update(
            $data
        );

        return response([
            "message" => "Transaksi Di Update Sudah bayar",
            "data" => $transaksi
        ], 200);
    }


    public function konfirmasiMO(Request $request, $id)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            "status" => "required", //valid atau tidak
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        //ketika pesanan ditolak
        if ($data['status'] == 'ditolak') {
            //balikin stoknya

            $transaksi->status_transaksi = 'ditolak';
            $transaksi->save();
            return response([
                "message" => "Transaksi Di Tolak MO",
                "data" => $transaksi
            ], 200);
        } else if ($data['status'] == 'diterima') {

            $transaksi->status_transaksi = 'diterima';
            $transaksi->save();

            // if($data['jenis_pesanan'] == 'ready stok'){
            //     if($['jenis'])
            // }

            return response([
                "message" => "Transaksi Di Diterima",
                "data" => $transaksi
            ], 200);
        } else if ($data['status'] == 'diproses') {
            if ($transaksi['jumlah_pembayaran'] > $transaksi['total_harga_transaksi']) {
                $transaksi['tip'] = $transaksi['jumlah_pembayaran'] - $transaksi['total_harga_transaksi'];
            }
            $transaksi->status_transaksi = 'diproses';
            $transaksi->save();
            return response([
                "message" => "Transaksi Di Diproses",
                "data" => $transaksi
            ], 200);
        }

        return response([
            "message" => "status yang diminta tidak valid!!",
            "data" => $transaksi
        ], 400);
    }


    public function konfirmasiAdmin(Request $request, $id)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            "status" => "required", //valid atau tidak
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        if ($data['status'] == 'diambil') {
            if ($transaksi['jenis_pengiriman'] == 'pick up') {
                //balikin stoknya
                $transaksi->status_transaksi = 'di-pickup';
            } else {
                $transaksi->status_transaksi = 'dikirim kurir';
            }
            $transaksi->save();
            return response([
                "message" => "Pesanan Berhasil Di Pick-up/dikirim",
                "data" => $transaksi
            ]);
        } else if ($data['status'] == "sudah di-pickup") {
            $transaksi->status_transaksi = 'sudah di-pickup';
            $transaksi->save();
            return response([
                "message" => "Pesanan Berhasil Di Pick-up",
                "data" => $transaksi
            ]);
        } else if ($data['status'] == "pembayaran valid") {
            $validate = Validator::make($data, [
                "jumlah_pembayaran" => "required",
                "status" => "required", //valid atau tidak valid
            ]);
            if ($validate->fails()) {
                return response(['message' => $validate->errors()->first()], 400);
            }

            if (!($transaksi['status_transaksi'] == 'sudah dibayar')) {

                return response([
                    "message" => "pembayaran is not valid",
                ], 400);
            }

            if ($data['jumlah_pembayaran'] < $transaksi['total_harga_transaksi']) {

                return response([
                    "message" => "Pembayaran Masih Kurang",
                    "total" => $transaksi['total_harga_transaksi'],
                    "uang_anda" =>   $data['jumlah_pembayaran']
                ]);
            }
            $transaksi->status_transaksi = 'pembayaran valid';
            $transaksi->jumlah_pembayaran = $data["jumlah_pembayaran"];
            $transaksi['tanggal_pelunasan'] = now();
            $transaksi->save();

            return response([
                "message" => "Transaksi Di Update Pembayaran Valid",
                "data" => $transaksi
            ], 200);
        } else if ($data['status'] == "pembayaran tidak valid") {
            $transaksi->status_transaksi = 'pembayaran tidak valid';
            $transaksi->save();

            return response([
                "message" => "Transaksi Di Update Pembayaran Tidak Valid",
                "data" => $transaksi
            ], 200);
        } else if ($data['status'] == "input biaya pengiriman") {
            $validate = Validator::make($data, [
                "radius" => "required",
            ]);
            if ($validate->fails()) {
                return response(['message' => $validate->errors()->first()], 400);
            }

            $transaksi->status_transaksi = 'menunggu pembayaran';
            $transaksi->biaya_pengiriman = $data['radius'] * 10000; // 10k per km
            $transaksi->total_harga_transaksi += $transaksi->biaya_pengiriman;
            $transaksi->save();

            return response([
                "message" => "Transaksi Di Update Pembayaran Tidak Valid",
                "data" => $transaksi
            ], 200);
        }
    }

    public function doneTransaksi($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response(['message' => 'Absensi not found'], 404);
        }

        $data['status_transaksi'] = "selesai";

        $point = Point::find($transaksi['id_user']);

        $point->jumlah_point += $transaksi['point_diperoleh'];
        $point->save();

        return response([
            "message" => "Pesanan Berhasil Diambil",
            "data" => $transaksi
        ]);
    }

    public function cetakNota($id)
    {

        $transaksi = Transaksi::select()
            ->where('id_transaksi', $id)
            ->first();

        if ($transaksi['jenis_pengiriman'] == "Atma Kitchen Delivery") {
            $transaksi = Transaksi::select('transaksi.*', 'u.email', 'u.nama_lengkap', 'p.*', 'a.*')
                ->join('users as u', 'u.id_user', 'transaksi.id_user')
                ->join('point as p', 'p.id_user', 'u.id_user')
                ->join('alamat as a', 'a.id_alamat', 'transaksi.id_alamat')
                ->where('transaksi.id_transaksi', $id)
                ->first();
            $data['alamat'] = $transaksi['alamat']['detail_alamat'] . ', ' . $transaksi['alamat']['kelurahan'] . ', ' . $transaksi['alamat']['kecamatan'] . ', ' . $transaksi['alamat']['kabupaten'] . ', ' . $transaksi['alamat']['provinsi'];
        } else {
            $transaksi = Transaksi::select('transaksi.*', 'u.*', 'p.*')
                ->join('users as u', 'u.id_user', 'transaksi.id_user')
                ->join('point as p', 'p.id_user', 'u.id_user')
                ->where('transaksi.id_transaksi', $id)
                ->first();
        }

        if (!$transaksi) {
            return response(['message' => 'transaksi not found'], 404);
        }

        $data['no_nota'] = $transaksi['no_nota'];
        $data['tanggal_pesan'] = $transaksi['tanggal_pesan'];
        $data['tanggal_pelunasan'] = $transaksi['tanggal_pelunasan'];
        $data['tanggal_pengambilan'] = $transaksi['tanggal_pengambilan'];
        $data['email'] = $transaksi['email'];
        $data['nama_lengkap'] = $transaksi['nama_lengkap'];
        $data['total_sebelum_ongkir'] = $transaksi['biaya_pengiriman'] + $transaksi['total_harga_transaksi'];
        $data['ongkir'] = $transaksi['biaya_pengiriman'];
        $data['total_setelah_ongkir'] = $transaksi['total_harga_transaksi'];
        $data['point_terpakai'] = $transaksi['point_terpakai'];
        $data['total_potongan'] = $transaksi['point_terpakai'] * 100;
        $data['total'] = $transaksi['total_harga_transaksi'] - $data['total_potongan'];
        $data['point_diperoleh'] = $transaksi['point_diperoleh'];
        $data['point_customer'] = $transaksi['jumlah_point'];

        $data['produk'] = DetailTransaksi::select('detail_transaksi.*', 'p.*')
            ->join('produk as p', 'p.id_produk', 'detail_transaksi.id_produk')
            ->join('transaksi as t', 't.id_transaksi', 'detail_transaksi.id_transaksi')
            ->where('t.id_user', $id)
            ->get();

        return response([
            "message" => "show Nota successfully",
            "data" => $data
        ], 200);
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
