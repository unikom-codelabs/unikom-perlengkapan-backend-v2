<?php

namespace App\Http\Requests\Pengumuman;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengumumanRequest extends FormRequest
{
    public function rules()
    {
        return [
            'judul' => 'sometimes|string|max:255',
            'teks' => 'sometimes|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10048'
        ];
    }
}