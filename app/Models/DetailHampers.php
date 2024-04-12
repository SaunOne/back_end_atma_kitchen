<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailHampers extends Model
{
    protected $table = 'detail_hampers';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_produk',
        'jumlah_produk',
        'id_hampers',
    ];

    public function produkUtama()
    {
        return $this->belongsTo(ProdukUtama::class, 'id_produk');
    }
}
