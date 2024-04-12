<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;

    protected $fillable = [
        'id_ready_stok',
        'nama_produk',
        'harga',
        'quantity',
        'deskripsi',
        'image_produk',
        'jenis_produk',
    ];

    public function readyStok()
    {
        return $this->belongsTo(ReadyStok::class, 'id_ready_stok');
    }
}
