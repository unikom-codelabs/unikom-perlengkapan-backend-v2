<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AktivasiPengajuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'pengajuan' => [
                'id' => $this->pengajuan->id,
                'tipe' => $this->pengajuan->tipe,
                'semester' => $this->pengajuan->semester,
                'ujian' => $this->pengajuan->ujian,
            ],

            'aktif_mulai' => $this->aktif_mulai,
            'aktif_selesai' => $this->aktif_selesai,

            'tipe' => $this->tipe,

            'tahun_akademik' => $this->tahun_akademik,

            'created_at' => $this->created_at
        ];
    }
}
