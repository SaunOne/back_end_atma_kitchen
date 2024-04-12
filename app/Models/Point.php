<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'point';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'jumlah_point',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
