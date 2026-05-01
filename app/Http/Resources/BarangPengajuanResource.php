<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarangPengajuanResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'id' => $this->id,

            'barang' => $this->whenLoaded('barang', function () {
                return $this->barang->nama;
            }),

            'jumlah' => $this->jumlah,

            'jumlah_disetujui' => $this->jumlah_disetujui,

            'status' => $this->status,

        ];
    }
}
