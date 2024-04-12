<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'detail_alamat',
        'kode_pos',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
