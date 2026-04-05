<?php

namespace App\Http\Requests\ApproveBarangPengajuan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApproveBarangPengajuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jumlah_disetujui' => 'required|integer|min:0',

            'status' => 'required|in:pending,approved,rejected'
        ];
    }
}
