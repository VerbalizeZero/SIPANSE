<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'tahun_angkatan')) {
                $table->string('tahun_angkatan')->nullable()->after('id');
            }

            if (!Schema::hasColumn('siswas', 'kelas')) {
                $table->string('kelas')->nullable()->after('jenis_kelamin');
            }

            if (!Schema::hasColumn('siswas', 'email_ortu')) {
                $table->string('email_ortu')->nullable()->after('no_hp_ortu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'tahun_angkatan')) {
                $table->dropColumn('tahun_angkatan');
            }

            if (Schema::hasColumn('siswas', 'kelas')) {
                $table->dropColumn('kelas');
            }

            if (Schema::hasColumn('siswas', 'email_ortu')) {
                $table->dropColumn('email_ortu');
            }
        });
    }
};

