<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuFaktur extends Model
{
    // Tabel transaksi faktur milik Tata Usaha.
    protected $table = 'tu_fakturs';

    // Kolom yang boleh diisi mass assignment pada create/update faktur TU.
    protected $fillable = [
        'master_faktur_id',
        'target_type',
        'target_value',
        'tersedia_pada',
        'jatuh_tempo',
        'status',
    ];

    // Relasi ke master faktur dari Bendahara (nama faktur + nominal).
    public function masterFaktur(): BelongsTo
    {
        return $this->belongsTo(MasterFaktur::class);
    }
}
