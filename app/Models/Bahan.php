<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $table = 'bahan';
    protected $primaryKey = 'id_bahan';
    public $timestamps = false;

    protected $fillable = [
        'nama_bahan',
        'stok_bahan',
        'satuan',
    ];
}
