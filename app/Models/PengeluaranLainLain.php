<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranLainLain extends Model
{
    protected $table = 'pengeluaran_lain_lain';
    protected $primaryKey = 'id_pengeluaran_lain_lain';
    public $timestamps = false;

    protected $fillable = [
        'nama_pengeluaran',
        'tanggal',
        'jumlah_pengeluaran',
    ];
}
