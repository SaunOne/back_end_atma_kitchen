<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallet';
    protected $primaryKey = 'id_user';
    public $incrementing = false; // To ensure that the id_user is not auto-incrementing
    public $timestamps = false;

    protected $fillable = [
        'jumlah_saldo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
