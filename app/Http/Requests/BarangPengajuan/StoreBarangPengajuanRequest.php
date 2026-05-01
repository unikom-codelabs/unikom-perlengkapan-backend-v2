<?php

namespace App\Http\Requests\BarangPengajuan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBarangPengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_barang' => [
                'required',
                'exists:barang,id'
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1'
            ]

        ];
    }
}
