<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemakaianBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bahan_baku';
    protected $primaryKey = 'id_pemakaian_bahan_baku';
    public $incrementing = false; // To ensure that the id_user is not auto-incrementing
    public $timestamps = false;

    protected $fillable = [
        'id_bahan',
        'id_transaksi',
        'jumlah',
        'tanggal',
    ];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }
}
