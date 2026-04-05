<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('vendors');

        return [
            'nama' => "required|string|max:255|unique:vendors,nama,$id",
            'harga' => 'nullable|numeric|min:0'
        ];
    }
}
