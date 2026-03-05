<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKelas extends Model
{
    // Nama tabel custom (karena tidak mengikuti plural default Laravel "data_kelas").
    protected $table = 'data_kelas';

    // Kolom yang boleh diisi mass assignment dari form create/update.
    protected $fillable = [
        'tahun_angkatan',
        'kelas',
        'level',
        'wali_kelas',
    ];
}
