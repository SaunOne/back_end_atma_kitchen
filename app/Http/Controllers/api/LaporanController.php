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


class LaporanController extends Controller
{
    public function laporanBulananKeseluruhan($tahun)
    {
        // return response(["sdf"]);
        $data = DB::select("
        WITH bulan_tahun AS (
            SELECT 1 AS bulan, 'January' AS nama_bulan UNION ALL
            SELECT 2, 'February' UNION ALL
            SELECT 3, 'March' UNION ALL
            SELECT 4, 'April' UNION ALL
            SELECT 5, 'May' UNION ALL
            SELECT 6, 'June' UNION ALL
            SELECT 7, 'July' UNION ALL
            SELECT 8, 'August' UNION ALL
            SELECT 9, 'September' UNION ALL
            SELECT 10, 'October' UNION ALL
            SELECT 11, 'November' UNION ALL
            SELECT 12, 'December'
        )
        SELECT 
            b.nama_bulan AS bulan,
            COALESCE(t.jumlah_transaksi, 0) AS \"jumlah_transaksi\",
            COALESCE(t.jumlah_uang, 0) AS \"jumlah_uang\"
        FROM 
            bulan_tahun b
        LEFT JOIN (
            SELECT
                MONTH(t1.tanggal_pesan) AS bulan,
                COUNT(t1.id_transaksi) AS jumlah_transaksi,
                SUM(t1.total_harga_transaksi) AS jumlah_uang
            FROM 
                transaksi t1
            WHERE 
                YEAR(t1.tanggal_pesan) = 2024
            AND 
                STATUS_TRANSAKSI = 'selesai'
            GROUP BY 
                MONTH(t1.tanggal_pesan)
        ) t ON b.bulan = t.bulan
        ORDER BY 
            b.bulan;
    ");

        $laporan['alamat'] = "Jl. Babarari ...";
        $laporan['tahun'] = $tahun;
        $now = Carbon::now();
        $laporan['tanggal_cetak'] = $now->isoFormat('D MMMM YYYY');
        $laporan['data'] = $data;
        $laporan['total'] = 0;
        foreach($data as $d){
            $laporan['total'] +=$d->jumlah_uang;
        }

        return response([
            'message' => 'Batalkan Transaksi successfully',
            'data' => $laporan
        ], 200);
    }

    public function tampilLaporanPenjualanPerProduk(){
        
    }
}


