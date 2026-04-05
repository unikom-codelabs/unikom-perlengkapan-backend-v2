<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'daftar_pengajuan_id',
    'nama',
    'jumlah',
    'jumlah_disetujui',
    'kategori',
    'satuan',
    'status'
])]

class BarangPengajuanLainnya extends Model
{

    protected $table = 'barang_pengajuan_lainnya';

    public $timestamps = false;

    public function daftarPengajuan()
    {
        return $this->belongsTo(DaftarPengajuan::class);
    }
}
