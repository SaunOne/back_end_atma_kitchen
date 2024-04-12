<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukUtama extends Model
{
    protected $table = 'produk_utama';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;

    protected $fillable = [
        'id_produk',
        'id_packaging',
        'kategori_produk',
        'limit_harian',
    ];

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'id_packaging');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
