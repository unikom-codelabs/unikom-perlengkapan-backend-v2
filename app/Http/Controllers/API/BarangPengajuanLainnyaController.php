<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BarangPengajuanLainnya\StoreBarangPengajuanLainnyaRequest;
use App\Http\Requests\BarangPengajuanLainnya\UpdateBarangPengajuanLainnyaRequest;
use App\Http\Resources\BarangPengajuanLainnyaResource;
use App\Models\BarangPengajuanLainnya;
use Illuminate\Http\Request;

class BarangPengajuanLainnyaController extends Controller
{

    public function store(StoreBarangPengajuanLainnyaRequest $request)
    {
        $data = $request->validated();

        $data['jumlah_disetujui'] = null;

        $data['status'] = 0;

        $barang = BarangPengajuanLainnya::create($data);

        return ApiResponse::success(
            $barang,
            'Barang manual berhasil ditambahkan'
        );
    }

    public function update(
        UpdateBarangPengajuanLainnyaRequest $request,
        BarangPengajuanLainnya $barangPengajuanLainnya
    ) {

        $barangPengajuanLainnya->update(

            $request->validated()

        );

        return ApiResponse::success(

            new BarangPengajuanLainnyaResource(

                $barangPengajuanLainnya

            )

        );
    }

    public function approve(Request $request, BarangPengajuanLainnya $barangPengajuanLainnya)
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

        $barangPengajuanLainnya->update([

            'jumlah_disetujui' => $validated['jumlah_disetujui'],

            'status' => $validated['status']

        ]);

        return ApiResponse::success(

            $barangPengajuanLainnya,

            'Barang manual berhasil di approve'

        );
    }

    public function destroy(
        BarangPengajuanLainnya $barangPengajuanLainnya
    ) {

        $barangPengajuanLainnya->delete();

        return ApiResponse::deleted();
    }
}