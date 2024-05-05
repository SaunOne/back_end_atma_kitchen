<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_packaging',
        'id_alamat',
        'total_harga_transaksi',
        'tanggal_pesan',
        'tanggal_pengambilan',
        'jenis_pesanan',
        'point_terpakai',
        'status_transaksi',
        'tanggal_pelunasan',
        'jumlah_pembayaran',
        'jenis_pengiriman',
        'biaya_pengiriman',
        'bukti_pembayaran',
        'point_diperoleh,'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'id_packaging');
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat');
    }
}
