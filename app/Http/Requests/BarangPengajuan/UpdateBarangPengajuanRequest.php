<?php

namespace App\Http\Requests\BarangPengajuan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangPengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'jumlah' => [
                'sometimes',
                'integer',
                'min:1'
            ],

            'jumlah_disetujui' => [
                'sometimes',
                'integer',
                'min:0'
            ],

            'status' => [
                'sometimes',
                'boolean'
            ]

        ];
    }
}
