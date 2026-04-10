<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyerahanFaktur extends Model
{
    use HasFactory;

    // Mass assignment
    protected $fillable = [
        'tu_faktur_id',
        'siswa_id',
        'berkas_file',
        'status',
        'catatan_penolakan',
    ];

    /**
     * Relasi ke data Faktur dari Tata Usaha
     */
    public function tuFaktur(): BelongsTo
    {
        return $this->belongsTo(TuFaktur::class, 'tu_faktur_id');
    }

    /**
     * Relasi ke data Siswa pelapor (Identitas Anak)
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
