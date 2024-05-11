<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    
    public $incrementing = false; // To ensure that the id_user is not auto-incrementing
    public $timestamps = false;
    

    protected $fillable = [
        'gaji',
        'bonus_gaji',
        'jabatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}