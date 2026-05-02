<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE barang_pengajuan_lainnya 
            MODIFY kategori ENUM('habis pakai','tidak habis pakai')
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE barang_pengajuan_lainnya 
            MODIFY kategori ENUM('habis_pakai','tidak_habis_pakai')
        ");
    }
};
