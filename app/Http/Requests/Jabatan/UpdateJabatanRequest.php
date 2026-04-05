<?php

namespace App\Http\Requests\Jabatan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nama' => 'sometimes|string|max:100|unique:jabatans,nama,' . $this->id
        ];
    }
}
