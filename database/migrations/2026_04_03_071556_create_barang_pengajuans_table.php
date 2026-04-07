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
        Schema::create('barang_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daftar_pengajuan_id')->constrained('daftar_pengajuan');
            $table->foreignId('id_barang')->constrained('barang');
            $table->integer('jumlah');
            $table->integer('jumlah_disetujui')->default(0);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_pengajuan');
    }
};
