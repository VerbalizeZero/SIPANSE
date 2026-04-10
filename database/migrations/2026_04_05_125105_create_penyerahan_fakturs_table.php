<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penyerahan_fakturs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tu_faktur_id')->constrained()->cascadeOnDelete(); // Relasi ke tabel faktur TU
            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete(); // Relasi identitas siswa yang menyerahkan
            $table->string('berkas_file'); // Path penyimpanan bukti berkas yang diunggah
            $table->string('status')->default('menunggu_verifikasi'); // Status: menunggu_verifikasi, diverifikasi, ditolak
            $table->text('catatan_penolakan')->nullable(); // Jika ditolak oleh TU
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyerahan_fakturs');
    }
};
