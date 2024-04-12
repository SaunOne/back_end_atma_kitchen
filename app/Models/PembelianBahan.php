<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBahan extends Model
{
    protected $table = 'pembelian_bahan';
    protected $primaryKey = 'id_pembelian_bahan';
    public $timestamps = false;

    protected $fillable = [
        'id_bahan',
        'jumlah',
        'harga_beli',
        'tanggal',
    ];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }
}
