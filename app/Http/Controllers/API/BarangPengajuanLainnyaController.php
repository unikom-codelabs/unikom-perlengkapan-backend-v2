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

    public function approve(Request $request, BarangPengajuanLainnya $barangPengajuanLainnya)
    {
        $validated = $request->validate([

            'jumlah_disetujui' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'boolean',
            ],

        ]);

        $barangPengajuanLainnya->update($validated);

        return ApiResponse::success(
            new BarangPengajuanLainnyaResource($barangPengajuanLainnya),
            'Barang manual berhasil di approve'
        );
    }

}
