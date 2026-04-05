<?php

namespace App\Http\Requests\DaftarPengajuan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDaftarPengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [

            'aktivasi_pengajuan_id' => [
                'required',
                'exists:aktivasi_pengajuan,id'
            ],

            'date' => [
                'required',
                'date'
            ],

            'surat_pengajuan' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:2048'
            ]

        ];
    }
}
