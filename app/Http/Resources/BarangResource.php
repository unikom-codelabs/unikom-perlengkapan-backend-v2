<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'tipe' => $this->tipe,
            'unit' => $this->unit,
            'vendor' => [
                'id' => $this->vendor?->id,
                'nama' => $this->vendor?->nama
            ],

            'created_at' => $this->created_at
        ];
    }
}
