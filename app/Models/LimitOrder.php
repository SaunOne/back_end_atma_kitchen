<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimitOrder extends Model
{
    use HasFactory;

    protected $table = 'limit_order';
    protected $primaryKey = 'id_limit';
    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'jumlah_sisa',
        'limit_harian',
    ];

    public function Produk()
    {
        return $this->belongsTo(Packaging::class, 'id_produk');
    }

  
}

