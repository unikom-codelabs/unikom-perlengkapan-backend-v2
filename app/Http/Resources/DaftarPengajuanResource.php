<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DaftarPengajuanResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'id' => $this->id,

            'user' => $this->user?->username,

            'aktivasi_pengajuan' => $this->aktivasi?->tipe,

            'surat_pengajuan' => $this->surat_pengajuan,

            'date' => $this->date,

            'created_at' => $this->created_at,

            'barang' => BarangPengajuanResource::collection(
                $this->whenLoaded('barang')
            ),

            'barang_lainnya' => BarangPengajuanLainnyaResource::collection(
                $this->whenLoaded('barangLainnya')
            ),

        ];
    }
}
