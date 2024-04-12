<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukTitipan extends Model
{
    protected $table = 'produk_titipan';
    protected $primaryKey = 'id_produk_titipan';
    public $timestamps = false;

    protected $fillable = [
        'id_produk',
        'id_penitip',
        'jumlah_produk_dititip',
        'tanggal',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function penitip()
    {
        return $this->belongsTo(Penitip::class, 'id_penitip');
    }
}
