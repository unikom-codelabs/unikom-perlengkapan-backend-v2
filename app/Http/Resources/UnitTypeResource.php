<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'parent_id' => $this->parent_id,

            'children' => UnitTypeResource::collection(
                $this->whenLoaded('children')
            )
        ];
    }
}
