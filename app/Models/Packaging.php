<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $table = 'packaging';
    protected $primaryKey = 'id_packaging';
    public $timestamps = false;

    protected $fillable = [
        'nama_packaging',
    ];
}
