<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadyStok extends Model
{
    protected $table = 'ready_stok';
    protected $primaryKey = 'id_stok_produk';
    public $timestamps = false;

    protected $fillable = [
        'jumlah_stok',
        'satuan',
        'nama_produk_stok'
    ];
}
