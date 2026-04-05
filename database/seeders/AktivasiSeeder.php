<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AktivasiPengajuan;

class AktivasiSeeder extends Seeder
{
    public function run(): void
    {

        AktivasiPengajuan::create([

            'id_pengajuan' => 1,

            'aktif_mulai' => now()->subDays(7),

            'aktif_selesai' => now()->addDays(7),

            'tipe' => 'rutin',

            'tahun_akademik' => '2025/2026'

        ]);
    }
}
