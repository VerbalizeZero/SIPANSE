<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'tahun_angkatan',
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'kelas',
        'tanggal_lahir',
        'alamat',
        'nama_ortu',
        'no_hp_ortu',
        'email_ortu',
    ];
}
