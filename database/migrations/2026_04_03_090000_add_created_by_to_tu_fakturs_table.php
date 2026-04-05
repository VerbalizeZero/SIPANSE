<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Menambahkan pembuat faktur TU untuk kebutuhan audit trail verifikasi.
    public function up(): void
    {
        Schema::table('tu_fakturs', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('master_faktur_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    // Rollback kolom pembuat faktur.
    public function down(): void
    {
        Schema::table('tu_fakturs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
