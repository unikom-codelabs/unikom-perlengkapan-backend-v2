<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kontak_person' => $this->kontak_person,
            'alamat' => $this->alamat,
            'created_at' => $this->created_at,
        ];
    }
}