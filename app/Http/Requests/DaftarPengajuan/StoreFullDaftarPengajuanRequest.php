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
        // parse JSON dari FormData
        $this->merge([
            'barang' => json_decode($this->input('barang'), true),
            'barang_lainnya' => json_decode($this->input('barang_lainnya'), true),
        ]);
    }

    public function rules(): array
    {
        return [

            // pengajuan utama
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
            ],

            // barang dari master
            'barang' => [
                'nullable',
                'array'
            ],

            'barang.*.id_barang' => [
                'required',
                'exists:barang,id'
            ],

            'barang.*.jumlah' => [
                'required',
                'integer',
                'min:1'
            ],

            // barang lainnya
            'barang_lainnya' => [
                'nullable',
                'array'
            ],

            'barang_lainnya.*.nama' => [
                'required',
                'string',
                'max:255'
            ],

            'barang_lainnya.*.jumlah' => [
                'required',
                'integer',
                'min:1'
            ],

            'barang_lainnya.*.kategori' => [
                'required',
                'in:habis pakai,tidak habis pakai'
            ],

            'barang_lainnya.*.satuan' => [
                'required',
                'string',
                'max:100'
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'aktivasi_pengajuan_id.required' => 'Periode pengajuan wajib dipilih.',
            'aktivasi_pengajuan_id.exists' => 'Periode pengajuan tidak valid.',

            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',

            'barang.*.id_barang.required' => 'Barang wajib dipilih.',
            'barang.*.id_barang.exists' => 'Barang tidak ditemukan.',
            'barang.*.jumlah.min' => 'Jumlah minimal 1.',

            'barang_lainnya.*.kategori.in' => 'Kategori harus habis pakai atau tidak habis pakai.',
        ];
    }
}