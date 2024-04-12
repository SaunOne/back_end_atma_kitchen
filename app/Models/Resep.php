<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    use HasFactory;

    protected $table = 'resep';
    public $timestamps = false;

    protected $fillable = [
        'id_produk',
        'id_bahan',
        'jumlah_bahan'
    ];

    public function produkUtama()
    {
        return $this->belongsTo(ProdukUtama::class, 'id_produk');
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }
}
