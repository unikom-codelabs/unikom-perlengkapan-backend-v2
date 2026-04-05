<?php

namespace App\Http\Requests\Pengumuman;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePengumumanRequest extends FormRequest
{
    public function rules()
    {
        return [
            'judul' => 'required|string|max:255',
            'teks' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10048'
        ];
    }
}
