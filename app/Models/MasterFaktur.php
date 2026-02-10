<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFaktur extends Model
{
    protected $fillable = [
        'jenis_faktur',
        'nama_faktur',
        'nominal',
        'deskripsi',
    ];
}
