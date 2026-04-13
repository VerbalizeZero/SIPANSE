<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MasterFaktur extends Model
{
    use LogsActivity;

    protected $fillable = [
        'jenis_faktur',
        'nama_faktur',
        'nominal',
        'deskripsi',
    ];
}
