<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Penitip;
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
                YEAR(t1.tanggal_pesan) = $tahun
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
        $laporan['tanggal_cetak'] = Carbon::now()->format('y-F-d');
        $laporan['data'] = $data;
        $laporan['total'] = 0;
        foreach ($data as $d) {
            $laporan['total'] += $d->jumlah_uang;
        }

        return response([
            'message' => 'Create Laporan successfully',
            'data' => $laporan
        ], 200);
    }

    public function tampilLaporanPenjualanPerProduk($tanggal)
    {
        
        $carbonDate = Carbon::parse($tanggal);

        $data = DB::table('produk as P1')
            ->leftJoinSub(
                DB::table('detail_transaksi as dt')
                    ->join('transaksi as t', 't.id_transaksi', '=', 'dt.id_transaksi')
                    ->select('dt.id_produk', DB::raw('COUNT(dt.id_produk) AS kuantitas'))
                    ->where('t.status_transaksi', 'selesai')
                    ->where(DB::raw('DATE_FORMAT(t.tanggal_pesan, "%Y-%m")'), $tanggal)
                    ->groupBy('dt.id_produk'),
                'kuantitas_cte',
                'P1.id_produk',
                '=',
                'kuantitas_cte.id_produk'
            )
            ->select(
                'P1.nama_produk',
                'P1.harga',
                DB::raw('COALESCE(kuantitas_cte.kuantitas, 0) AS kuantitas'),
                DB::raw('(P1.harga * COALESCE(kuantitas_cte.kuantitas, 0)) AS jumlah_uang')
            )
            ->get();

        $laporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $laporan['bulan'] = $carbonDate->translatedFormat('F');
        $laporan['tahun'] = $carbonDate->translatedFormat('Y');
        $laporan['tanggal_cetak'] = Carbon::now()->format('y-F-d');
        $total = 0;
        foreach($data as $d){
            $total+=$d->jumlah_uang;
        }

        
        $laporan['data'] = $data;
        $laporan['Total'] = $total;

        return response([
            "data" => $laporan
        ]);
    }

    public function laporanPemakaianBahanBaku(Request $request)
    {

        $data = $request->all();

        $validate = Validator::make($data, [
            "start_date" => "required",
            "end_date" => "required",
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $isi = DB::table('bahan as b1')
            ->select(
                'b1.nama_bahan',
                'b1.satuan',
                DB::raw('COALESCE((select sum(pbb.jumlah) from pemakaian_bahan_baku pbb where pbb.id_bahan = b1.id_bahan and pbb.tanggal >= "' . $data['start_date'] . '" and pbb.tanggal <= "' . $data['end_date'] . '"), 0) as digunakan')
            )
            ->get();

        $laporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $laporan['periode'] = $data['start_date'] . ' - ' . $data['end_date'];
        $laporan['tanggal_cetak'] = Carbon::now()->format('y F d');
        $laporan['data'] = $isi;
        $total = 0;
        
        
        return response([
            "data" => $laporan
        ]);
    }

    public function laporanStokBahanBaku()
    {

        $data = Bahan::select()->get();

        $laporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $laporan['tanggal_cetak'] = Carbon::now()->format('y F d');
        $laporan['data'] = $data;

        return response([
            "data" => $laporan
        ]);
    }

    public function laporanPengeluaranPemasukkan($tanggal)
    {
        $penjualan = DB::table('transaksi')
            ->where('status_transaksi', 'selesai')
            ->where(DB::raw('DATE_FORMAT(tanggal_pesan, "%Y-%m")'), $tanggal)
            ->sum('total_harga_transaksi');

        $tip = DB::table('transaksi')
            ->where('status_transaksi', 'selesai')
            ->where(DB::raw('DATE_FORMAT(tanggal_pesan, "%Y-%m")'), $tanggal)
            ->sum('tip');

        $bahanBaku = DB::table('pembelian_bahan')
            ->where(DB::raw('DATE_FORMAT(tanggal, "%Y-%m")'), $tanggal)
            ->sum('harga_beli');

        $pengeluaran = DB::table('pengeluaran_lain_lain')
            ->select(
                'nama_pengeluaran as nama',
                DB::raw('0 as pemasukan'),
                'jumlah_pengeluaran as pengeluaran'
            )
            ->where(DB::raw('DATE_FORMAT(tanggal, "%Y-%m")'), $tanggal)
            ->get();

        $date = Carbon::createFromFormat('Y-m', $tanggal);
        $daysInMonth = $date->daysInMonth;
        $today = Carbon::now()->format('y-m');
        if ($date->format('y-m') == $today) {
            $daysInMonth = Carbon::now()->day;
        }

        $gajiPegawai = DB::table('pegawai as p1')
            ->select(DB::raw('SUM(p1.gaji * (' . $daysInMonth . ' - (
                SELECT COUNT(*) 
                FROM absensi 
                WHERE absensi.id_user = p1.id_user 
                AND DATE_FORMAT(absensi.tanggal, "%Y-%m") = "' . $tanggal . '"
            ))) AS total_gaji'))
            ->first();

        $carbonDate = Carbon::parse($tanggal);

        $laporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $laporan['bulan'] = $carbonDate->translatedFormat('F');
        $laporan['tahun'] = $carbonDate->translatedFormat('Y');
        $laporan['tanggal_cetak'] = Carbon::now()->format('y F d');

        $isi = [
            [
                "nama" => "Penjualan",
                "pemasukan" => $penjualan,
                "pengeluaran" => 0
            ],
            [
                "nama" => "Tip",
                "pemasukan" => $tip,
                "pengeluaran" => 0
            ],
            [
                "nama" => "Bahan Baku",
                "pemasukan" => 0,
                "pengeluaran" => $bahanBaku
            ],
            [
                "nama" => "Gaji Karyawan",
                "pemasukan" => 0,
                "pengeluaran" => $gajiPegawai->total_gaji
            ]
        ];

        foreach ($pengeluaran as $p) {
            $isi[] = (array)$p;
        }

        $total_pengeluaran = 0;
        $total_pemasukan = 0;
        foreach ($isi as $l) {
            $total_pengeluaran += $l['pengeluaran'];
            $total_pemasukan += $l['pemasukan'];
        }

        $isi[] = [
            "nama" => "Total",
            "pemasukan" => $total_pemasukan,
            "pengeluaran" => $total_pengeluaran
        ];

        $laporan['data'] = $isi;

        return response([
            "data" => $laporan

        ]);
    }

    public function laporanKaryawan($tanggal)
    {
        $date = Carbon::createFromFormat('Y-m', $tanggal);
        $daysInMonth = $date->daysInMonth;
        $data = DB::table('pegawai as p1')
            ->join('users as u', 'u.id_user', '=', 'p1.id_user')
            ->select(
                'u.nama_lengkap as nama',
                DB::raw('(' . $daysInMonth . ' - (
                SELECT 
                    COUNT(*) 
                FROM 
                    absensi 
                WHERE 
                    absensi.id_user = p1.id_user 
                    AND DATE_FORMAT(absensi.tanggal, "%Y-%m") = "' . $tanggal . '"
            )) as jumlah_hadir'),
                DB::raw('(
                SELECT 
                    COUNT(*) 
                FROM 
                    absensi 
                WHERE 
                    absensi.id_user = p1.id_user 
                    AND DATE_FORMAT(absensi.tanggal, "%Y-%m") = "' . $tanggal . '"
            ) as jumlah_bolos'),
                DB::raw('(
                (SELECT 
                    COUNT(*) 
                FROM 
                    absensi 
                WHERE 
                    absensi.id_user = p1.id_user 
                    AND DATE_FORMAT(absensi.tanggal, "%Y-%m") = "' . $tanggal . '") * p1.gaji
            ) as honor_harian'),
                'p1.bonus_gaji',
                DB::raw('(p1.bonus_gaji + ((SELECT 
                    COUNT(*) 
                FROM 
                    absensi 
                WHERE 
                    absensi.id_user = p1.id_user 
                    AND DATE_FORMAT(absensi.tanggal, "%Y-%m") = "' . $tanggal . '") * p1.gaji)) as total')
            )
            ->get();

        foreach ($data as $d) {
            if ($d->jumlah_bolos > 4) {
                $d->total -= $d->bonus_gaji;
                $d->bonus_gaji = 0;
            }
        }
        $carbonDate = Carbon::parse($tanggal);
        $laporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $laporan['bulan'] = $carbonDate->translatedFormat('F');
        $laporan['tahun'] = $carbonDate->translatedFormat('Y');
        $laporan['tanggal_cetak'] = Carbon::now()->format('y F d');
        $laporan['data'] = $data;

        return response([
            "message" => "show laporan successfully",
            "data" => $laporan
        ]);
    }

    public function laporanPenitip($tanggal)
    {

        $penitip = Penitip::all();
        $isi = [];

        $carbonDate = Carbon::parse($tanggal);
        $dLaporan['alamat'] = "jl.Centralpark No. 10 Yogyakarta";
        $dLaporan['bulan'] = $carbonDate->translatedFormat('F');
        $dLaporan['tahun'] = $carbonDate->translatedFormat('Y');
        $dLaporan['tanggal_cetak'] = Carbon::now()->format('y F d');
        foreach ($penitip as $p) {
            
            $data = DB::table('penitip as p1')
                ->join('produk_titipan as pt', 'pt.id_penitip', '=', 'p1.id_penitip')
                ->join('produk as p2', 'p2.id_produk', '=', 'pt.id_produk')
                ->select(
                    'p2.nama_produk',
                    DB::raw('COALESCE((select sum(dt.jumlah_produk) from detail_transaksi dt join transaksi t on(t.id_transaksi = dt.id_transaksi) where dt.id_produk = p2.id_produk and t.status_transaksi = "selesai" AND DATE_FORMAT(t.tanggal_pesan, "%Y-%m") = "2024-05"), 0) as qty'),
                    'p2.harga as harga_jual',
                    DB::raw('COALESCE(((select sum(dt.jumlah_produk) from detail_transaksi dt join transaksi t on(t.id_transaksi = dt.id_transaksi) where dt.id_produk = p2.id_produk and t.status_transaksi = "selesai" AND DATE_FORMAT(t.tanggal_pesan, "%Y-%m") )*p2.harga), 0) as total'),
                    DB::raw('COALESCE((((select sum(dt.jumlah_produk) from detail_transaksi dt join transaksi t on(t.id_transaksi = dt.id_transaksi) where dt.id_produk = p2.id_produk and t.status_transaksi = "selesai" AND DATE_FORMAT(t.tanggal_pesan, "%Y-%m"))*p2.harga) * 20/100), 0) as komisi'),
                    DB::raw('COALESCE(((((select sum(dt.jumlah_produk) from detail_transaksi dt join transaksi t on(t.id_transaksi = dt.id_transaksi) where dt.id_produk = p2.id_produk and t.status_transaksi = "selesai" AND DATE_FORMAT(t.tanggal_pesan, "%Y-%m"))*p2.harga) * 80/100)), 0) as yang_diterima')
                )
                ->where('p1.id_penitip',$p->id_penitip)
                ->get();
            $dLaporan['id_penitip'] = $p->id_penitip;
            $dLaporan['nama_penitip'] = $p->nama_penitip;
            $dLaporan['data'] = $data;
            $isi[] = $dLaporan;
        }
        
        $laporan['data'] = $isi;

        return response([
            "message" => "show laporan successfully",
            "data" => $laporan
        ]);
    }
}