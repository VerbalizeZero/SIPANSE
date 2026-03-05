<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Membuat tabel metadata kelas (level + wali kelas) per kombinasi angkatan dan kelas.
    public function up(): void
    {
        Schema::create('data_kelas', function (Blueprint $table) {
            $table->id();
            // Mengikuti tipe data dari proses import siswa (string tahun angkatan).
            $table->string('tahun_angkatan', 20);
            $table->string('kelas', 50);
            $table->string('level', 20)->nullable();
            $table->string('wali_kelas')->nullable();
            $table->timestamps();

            // Satu baris metadata unik untuk satu angkatan + satu kelas.
            $table->unique(['tahun_angkatan', 'kelas']);
        });
    }

    // Rollback: hapus tabel data_kelas.
    public function down(): void
    {
        Schema::dropIfExists('data_kelas');
    }
};
