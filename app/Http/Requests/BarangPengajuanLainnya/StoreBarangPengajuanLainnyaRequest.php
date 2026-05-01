<?php

namespace App\Http\Requests\BarangPengajuanLainnya;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangPengajuanLainnyaRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'daftar_pengajuan_id' => [
                'required',
                'exists:daftar_pengajuan,id',
            ],

            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'kategori' => [
                'required',
                'in:habis pakai,tidak habis pakai',
            ],

            'satuan' => [
                'required',
                'string',
                'max:100',
            ],

        ];
    }
}
