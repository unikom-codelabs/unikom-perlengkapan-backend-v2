<?php

namespace App\Http\Requests\DaftarPengajuan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDaftarPengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'surat_pengajuan' => [
                'nullable',
                'string',
                'max:255'
            ]

        ];
    }
}
