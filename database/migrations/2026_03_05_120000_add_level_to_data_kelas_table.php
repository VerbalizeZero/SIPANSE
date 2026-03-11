<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Menambah kolom level jika migrasi awal belum memilikinya.
    public function up(): void
    {
        Schema::table('data_kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('data_kelas', 'level')) { // Cek jika kolom level belum ada.
                $table->string('level', 20)->nullable()->after('kelas'); // Tambah kolom level setelah kolom kelas.
            }
        });
    }

    // Rollback: hapus kolom level jika ada.
    public function down(): void
    {
        Schema::table('data_kelas', function (Blueprint $table) {
            if (Schema::hasColumn('data_kelas', 'level')) { // Cek jika kolom level ada sebelum mencoba menghapusnya.
                $table->dropColumn('level'); // Hapus kolom level jika ada.
            }
        });
    }
};
