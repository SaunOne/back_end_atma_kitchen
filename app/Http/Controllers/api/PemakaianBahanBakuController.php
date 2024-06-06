<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Alamat;
use App\Models\Point;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Resep;
use App\Models\Bahan;
use App\Models\Hampers;
use App\Models\PemakaianBahanBaku;
use App\Models\DetailHampers;
use App\Models\LimitOrder;
use App\Models\ReadyStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengeluaranLainLain;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PemakaianBahanBakuController extends Controller
{
    public function showAll($date)
    {
        $transaksi = Transaksi::select('transaksi.*', 'users.nama_lengkap')
            ->join('users', 'users.id_user', 'transaksi.id_user')
            ->where('status_transaksi', 'diterima')
            ->where('tanggal_pengambilan', $date . ' 00:00:00')
            ->get();
            
        $merge_produk = [];
        
        foreach ($transaksi as $t){
            $detail_transaksi = DetailTransaksi::where('id_transaksi', $t->id_transaksi)->get();   
            $t->detail_transaksi = $detail_transaksi;
            foreach ($detail_transaksi as $dt){
                $produk = Produk::where('id_produk', $dt->id_produk)->get();
                $dt->produk = $produk;

                foreach($produk as $p){
                    if($p->jenis_produk == 'Utama'){
                        $temp = true;
                        
                        for ($i = 0; $i < count($merge_produk); $i++) {
                            if ($merge_produk[$i]['id_stok_produk'] == $p->id_stok_produk) {
                                $merge_produk[$i]['total_quantity'] += ($dt['jumlah_produk'] * $p['quantity']);
                                $temp = false;
                                break; 
                            }
                        }
                        
                        if($temp == true){
                            $total_quantity = $dt->jumlah_produk * $p->quantity;
                            $merge_produk[] = [
                                'id_stok_produk' => $p->id_stok_produk,
                                'total_quantity' => $total_quantity,
                                'nama_produk_stok' => ReadyStok::where('id_stok_produk', $p->id_stok_produk)->first()->nama_produk_stok,
                                'satuan' => ReadyStok::where('id_stok_produk', $p->id_stok_produk)->first()->satuan
                            ];
                        }
                    }
                    else 
                    if($p->jenis_produk == 'Hampers'){
                        
                        $detailHampers = DetailHampers::select()
                        ->join('produk_utama as pu', 'pu.id_produk', 'detail_hampers.id_produk')
                        ->join('produk as pr', 'pr.id_produk', 'pu.id_produk')
                        ->where('detail_hampers.id_hampers', $dt['id_produk'])
                        ->get();

                        foreach($detailHampers as $dh){
                            $temp = true;

                            for ($i = 0; $i < count($merge_produk); $i++) {
                                if ($merge_produk[$i]['id_stok_produk'] == $dh->id_stok_produk) {
                                    $merge_produk[$i]['total_quantity'] += ($dt['jumlah_produk'] * $dh['quantity']);
                                    $temp = false;
                                    break; 
                                }
                            }
                            
                            if($temp == true){
                                $total_quantity = $dt->jumlah_produk * $dh->quantity;
                                $merge_produk[] = [
                                    'id_stok_produk' => $dh->id_stok_produk,
                                    'total_quantity' => $total_quantity,
                                    'nama_produk_stok' => ReadyStok::where('id_stok_produk', $dh->id_stok_produk)->first()->nama_produk_stok,
                                    'satuan' => ReadyStok::where('id_stok_produk', $dh->id_stok_produk)->first()->satuan
                                ];
                            }
                        }   
                    }   
                }

                for ($i = 0; $i < count($merge_produk); $i++) {
                    
                    $resep = Resep::where('id_stok_produk', $merge_produk[$i]['id_stok_produk'])->get();
                    foreach($resep as $r){
                        $r->total_dibutuhkan = $merge_produk[$i]['total_quantity'] * $r->jumlah_bahan;
                        $selisih_bahan= Bahan::where('id_bahan', $r->id_bahan)->first()->stok_bahan - $r->total_dibutuhkan  ;
                        $r->nama_bahan = Bahan::where('id_bahan', $r->id_bahan)->first()->nama_bahan;
                        $r->satuan = Bahan::where('id_bahan', $r->id_bahan)->first()->satuan;
                        if($selisih_bahan < 0){
                            $r->kekurangan_bahan = $selisih_bahan;
                        }else{
                            $r->kekurangan_bahan = 0;
                        }
                        
                    }
                    $merge_produk[$i]['resep'] = $resep;
                }

            }    
        }

        for ($i = 0; $i < count($merge_produk); $i++) {
            if($merge_produk[$i]['total_quantity'] < 1){
                $merge_produk[$i]['total_quantity'] = 1;
            }
        }
        $rekap_bahan = [];
            
            foreach($merge_produk as $mp){
                foreach($mp['resep'] as $r){
                    $temp = true;
                    for ($i = 0; $i < count($rekap_bahan); $i++) {
                        if ($rekap_bahan[$i]['id_bahan'] == $r->id_bahan) {
                            $rekap_bahan[$i]['total_dibutuhkan'] += $r->total_dibutuhkan;
                            $rekap_bahan[$i]['kekurangan_bahan'] += $r->kekurangan_bahan;
                            $temp = false;
                            break; 
                        }
                    }
                    if($temp == true){
                        $rekap_bahan[] = [
                            'id_bahan' => $r->id_bahan,
                            'nama_bahan' => Bahan::where('id_bahan', $r->id_bahan)->first()->nama_bahan,
                            'satuan' => Bahan::where('id_bahan', $r->id_bahan)->first()->satuan,
                            'total_dibutuhkan' =>  $r->total_dibutuhkan,
                            'kekurangan_bahan' => $r->kekurangan_bahan
                        ];
                    }
                }
            }

            
        
        return response([
            'data' => $transaksi,
            'merge_produk' => $merge_produk,
            'rekap_bahan' => $rekap_bahan
        ]);
    }
}