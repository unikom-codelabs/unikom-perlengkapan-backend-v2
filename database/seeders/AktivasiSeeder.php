<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AktivasiPengajuan;

class AktivasiSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAkademik = '2025/2026';
        $mulai = now()->subDays(7);
        $selesai = now()->addDays(90);

        $pengajuanIds = \App\Models\Pengajuan::pluck('id', 'tipe');

        // Aktivasi untuk kelas ganjil (id_pengajuan: kelas ganjil)
        if ($pengajuanIds->has('kelas')) {
            AktivasiPengajuan::firstOrCreate(
                ['id_pengajuan' => $pengajuanIds['kelas'], 'tahun_akademik' => $tahunAkademik],
                [
                    'aktif_mulai' => $mulai,
                    'aktif_selesai' => $selesai,
                    'tipe' => 'rutin',
                    'tahun_akademik' => $tahunAkademik,
                ]
            );
        }

        // Aktivasi untuk ujian (ambil yang pertama bertipe ujian)
        if ($pengajuanIds->has('ujian')) {
            AktivasiPengajuan::firstOrCreate(
                ['id_pengajuan' => $pengajuanIds['ujian'], 'tahun_akademik' => $tahunAkademik],
                [
                    'aktif_mulai' => $mulai,
                    'aktif_selesai' => $selesai,
                    'tipe' => 'rutin',
                    'tahun_akademik' => $tahunAkademik,
                ]
            );
        }

        // Aktivasi untuk tahunan
        if ($pengajuanIds->has('tahunan')) {
            AktivasiPengajuan::firstOrCreate(
                ['id_pengajuan' => $pengajuanIds['tahunan'], 'tahun_akademik' => $tahunAkademik],
                [
                    'aktif_mulai' => $mulai,
                    'aktif_selesai' => $selesai,
                    'tipe' => 'rutin',
                    'tahun_akademik' => $tahunAkademik,
                ]
            );
        }

        // Aktivasi untuk nonrutin
        if ($pengajuanIds->has('nonrutin')) {
            AktivasiPengajuan::firstOrCreate(
                ['id_pengajuan' => $pengajuanIds['nonrutin'], 'tahun_akademik' => $tahunAkademik],
                [
                    'aktif_mulai' => $mulai,
                    'aktif_selesai' => $selesai,
                    'tipe' => 'nonrutin',
                    'tahun_akademik' => $tahunAkademik,
                ]
            );
        }
    }
}
