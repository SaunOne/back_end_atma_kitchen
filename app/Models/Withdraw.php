<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    protected $table = 'withdraw';
    protected $primaryKey = 'id_withdraw';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'jumlah_withdraw',
        'status_withdraw',
        'tanggal',
        'nama_bank',
        'no_rek',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
