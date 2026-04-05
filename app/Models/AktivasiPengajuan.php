<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'id_pengajuan',
    'aktif_mulai',
    'aktif_selesai',
    'tipe',
    'tahun_akademik'
])]

class AktivasiPengajuan extends Model
{
    use HasFactory;

    protected $table = 'aktivasi_pengajuan';

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan');
    }

    public function daftarPengajuan()
    {
        return $this->hasMany(DaftarPengajuan::class, 'id_aktivasi');
    }
}
