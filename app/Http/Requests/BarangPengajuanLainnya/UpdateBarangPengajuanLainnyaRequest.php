<?php

namespace App\Http\Requests\BarangPengajuanLainnya;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangPengajuanLainnyaRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'nama' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'jumlah' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'jumlah_disetujui' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'kategori' => [
                'sometimes',
                'in:habis pakai,tidak habis pakai',
            ],

            'satuan' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],

        ];
    }
}
