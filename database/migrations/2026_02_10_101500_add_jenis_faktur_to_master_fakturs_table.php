<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_fakturs', function (Blueprint $table) {
            $table->string('jenis_faktur')->default('Lainnya')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('master_fakturs', function (Blueprint $table) {
            $table->dropColumn('jenis_faktur');
        });
    }
};

