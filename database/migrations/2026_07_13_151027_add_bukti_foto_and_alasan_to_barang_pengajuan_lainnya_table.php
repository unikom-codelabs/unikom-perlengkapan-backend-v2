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
        Schema::table('barang_pengajuan_lainnya', function (Blueprint $table) {
            $table->string('bukti_foto')->nullable()->after('status');
            $table->text('alasan')->nullable()->after('bukti_foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_pengajuan_lainnya', function (Blueprint $table) {
            $table->dropColumn(['bukti_foto', 'alasan']);
        });
    }
};
