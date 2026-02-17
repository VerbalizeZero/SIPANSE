<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table ->string('tahun_angkatan')->nullable();
            $table->string('nisn', 20)->unique();
            $table->string('nama_siswa');
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('kelas')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ortu')->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('email_ortu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};

