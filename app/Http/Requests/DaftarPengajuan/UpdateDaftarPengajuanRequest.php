<?php

namespace App\Http\Requests\DaftarPengajuan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDaftarPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'date' => [
                'sometimes',
                'date'
            ],

            'surat_pengajuan' => [
                'sometimes',
                'file',
                'mimes:pdf,doc,docx',
                'max:2048'
            ]

        ];
    }

    public function messages(): array
    {
        return [

            'date.date' => 'Format tanggal tidak valid.',

            'surat_pengajuan.mimes' => 'File harus berupa PDF, DOC, atau DOCX.',
            'surat_pengajuan.max' => 'Ukuran file maksimal 2MB.'

        ];
    }
}