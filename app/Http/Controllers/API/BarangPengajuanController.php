<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BarangPengajuan\StoreBarangPengajuanRequest;
use App\Http\Requests\BarangPengajuan\UpdateBarangPengajuanRequest;
use App\Http\Resources\BarangPengajuanResource;
use App\Models\BarangPengajuan;
use Illuminate\Http\Request;

class BarangPengajuanController extends Controller
{

    public function store(StoreBarangPengajuanRequest $request)
    {

        $data = $request->validated();

        $data['jumlah_disetujui'] = null;

        $data['status'] = 0;

        $barang = BarangPengajuan::create($data);

        return ApiResponse::success($barang, 'Barang berhasil ditambahkan');
    }

    public function approve(Request $request, BarangPengajuan $barangPengajuan)
    {
        $validated = $request->validate([

            'jumlah_disetujui' => [
                'required',
                'integer',
                'min:0'
            ],

            'status' => [
                'required',
                'in:1,2'
            ]

        ]);

        $barangPengajuan->update([

            'jumlah_disetujui' => $validated['jumlah_disetujui'],

            'status' => $validated['status']

        ]);

        return ApiResponse::success(

            $barangPengajuan,

            'Barang berhasil di approve'

        );
    }

    public function update(
        UpdateBarangPengajuanRequest $request,
        BarangPengajuan $barangPengajuan
    ) {

        $barangPengajuan->update(

            $request->validated()

        );

        return ApiResponse::success(

            new BarangPengajuanResource(

                $barangPengajuan->load('barang')

            )

        );
    }

    public function destroy(BarangPengajuan $barangPengajuan)
    {

        $barangPengajuan->delete();

        return ApiResponse::deleted();
    }
}
