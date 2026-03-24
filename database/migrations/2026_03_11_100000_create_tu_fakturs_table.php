<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Membuat tabel transaksi faktur milik Tata Usaha.
    public function up(): void
    {
        Schema::create('tu_fakturs', function (Blueprint $table) {
            $table->id();
            // Referensi master faktur dari modul Bendahara.
            $table->foreignId('master_faktur_id')->constrained('master_fakturs')->cascadeOnDelete();
            $table->string('target_type', 20); // angkatan|kelas|semua_siswa|siswa
            // Nilai target menyesuaikan target_type (contoh: 2027, X-A, "Semua Siswa", atau identitas siswa).
            $table->string('target_value', 100)->nullable();
            // Periode berlaku faktur.
            $table->date('tersedia_pada');
            $table->date('jatuh_tempo');
            $table->string('status', 20)->default('Pending'); // Pending|Selesai
            $table->timestamps();
        });
    }

    // Rollback migration iterasi-05.
    public function down(): void
    {
        Schema::dropIfExists('tu_fakturs');
    }
};
