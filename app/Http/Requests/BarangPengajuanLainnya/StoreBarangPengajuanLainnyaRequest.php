<?php

namespace App\Http\Requests\BarangPengajuanLainnya;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBarangPengajuanLainnyaRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'nama' => [
                'required',
                'string',
                'max:255'
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1'
            ],

            'kategori' => [
                'required',
                'string',
                'max:255'
            ],

            'satuan' => [
                'required',
                'string',
                'max:100'
            ]

        ];
    }
}
