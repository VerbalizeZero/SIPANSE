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
        Schema::table('tu_fakturs', function (Blueprint $table) {
            $table->unsignedBigInteger('last_exported_by')->nullable()->after('last_exported_at');
            $table->foreign('last_exported_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tu_fakturs', function (Blueprint $table) {
            $table->dropForeign(['last_exported_by']);
            $table->dropColumn('last_exported_by');
        });
    }
};
