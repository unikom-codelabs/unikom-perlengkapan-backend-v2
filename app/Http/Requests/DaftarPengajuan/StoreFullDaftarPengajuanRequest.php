<?php

namespace App\Http\Requests\DaftarPengajuan;

use Illuminate\Foundation\Http\FormRequest;

class StoreFullDaftarPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'barang' => json_decode($this->input('barang') ?? '[]', true),
            'barang_lainnya' => json_decode($this->input('barang_lainnya') ?? '[]', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'tipe' => ['required', 'in:tahunan,ujian,kelas'],
             
            'date' => [
                'nullable',
                'date',
            ],

            'surat_pengajuan' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:2048',
            ],

            'barang' => [
                'nullable',
                'array',
            ],

            'barang.*.id_barang' => [
                'required',
                'exists:barang,id',
            ],

            'barang.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'barang_lainnya' => [
                'nullable',
                'array',
            ],

            'barang_lainnya.*.nama' => [
                'required',
                'string',
                'max:255',
            ],

            'barang_lainnya.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'barang_lainnya.*.kategori' => [
                'required',
                'in:habis_pakai,tidak_habis_pakai',
            ],

            'barang_lainnya.*.satuan' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'barang.*.id_barang.required' => 'Barang wajib dipilih.',
            'barang.*.id_barang.exists' => 'Barang tidak ditemukan.',
            'barang.*.jumlah.min' => 'Jumlah minimal 1.',

            'barang_lainnya.*.kategori.in' => 'Kategori harus habis pakai atau tidak habis pakai.',
        ];
    }
}
