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
        $barang = $this->input('barang', []);
        $barangLainnya = $this->input('barang_lainnya', []);

        $this->merge([
            'barang' => is_string($barang) ? json_decode($barang, true) : $barang,
            'barang_lainnya' => is_string($barangLainnya) ? json_decode($barangLainnya, true) : $barangLainnya,
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

            'barang_lainnya.*.alasan' => [
                'nullable',
                'string',
            ],

            'barang_lainnya.*.catatan' => [
                'nullable',
                'string',
            ],

            'barang_lainnya.*.note' => [
                'nullable',
                'string',
            ],

            'barang_lainnya.*.bukti_foto' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
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
