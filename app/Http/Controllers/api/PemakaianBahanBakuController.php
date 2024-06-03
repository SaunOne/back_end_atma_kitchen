<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemakaianBahanBaku;
use Illuminate\Support\Facades\DB;

class PemakaianBahanBakuController extends Controller
{
    public function tampilByTanggal($tanggal)
    {
        $pemakaianBahan = DB::table('bahan as b1')
            ->select(
                'b1.id_bahan',
                'b1.satuan',
                'b1.nama_bahan',
                'pbk.id_transaksi',
                DB::raw("(SELECT SUM(jumlah) FROM pemakaian_bahan_baku WHERE tanggal LIKE '%$tanggal%' AND id_bahan = b1.id_bahan) as jumlah"),
                'pbk.tanggal'
            )
            ->join('pemakaian_bahan_baku as pbk', 'pbk.id_bahan', '=', 'b1.id_bahan')
            ->where('pbk.tanggal', 'like', "%$tanggal%")
            ->get();

        $listPemakaianBahan = [];
        foreach ($pemakaianBahan as $pb) {
            $temp = true;
            foreach ($listPemakaianBahan as $lpb) {
                if ($lpb->id_bahan == $pb->id_bahan) {
                    $temp = false;
                }
            }
            if ($temp == true) {
                $listPemakaianBahan[] = $pb;
            }
        }

        return response([
            "data" => $listPemakaianBahan
        ]);
    }
    public function tampilAll()
    {
        $data = PemakaianBahanBaku::select(DB::raw('DATE(tanggal) as tanggal'))
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->get();
        
        $listAll = [];
        foreach ($data as $pbb) {
            $pemakaianBahan = DB::table('bahan as b1')
                ->select(
                    'b1.id_bahan',
                    'b1.satuan',
                    'b1.nama_bahan',
                    'pbk.id_transaksi',
                    DB::raw("(SELECT SUM(jumlah) FROM pemakaian_bahan_baku WHERE tanggal LIKE '%$pbb->tanggal%' AND id_bahan = b1.id_bahan) as jumlah"),
                    'pbk.tanggal'
                )
                ->join('pemakaian_bahan_baku as pbk', 'pbk.id_bahan', '=', 'b1.id_bahan')
                ->where('pbk.tanggal', 'like', "%$pbb->tanggal%")
                ->get();

            $listPemakaianBahan = [];
            foreach ($pemakaianBahan as $pb) {
                $temp = true;
                foreach ($listPemakaianBahan as $lpb) {
                    if ($lpb->id_bahan == $pb->id_bahan) {
                        $temp = false;
                    }
                }
                if ($temp == true) {
                    $listPemakaianBahan[] = $pb;
                }
            }
            $listAll = array_merge($listAll, $listPemakaianBahan);
        }

        return response([
            "data" => $listAll
        ]);
    }
}
