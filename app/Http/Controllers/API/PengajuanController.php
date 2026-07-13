<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengajuanResource;
use App\Models\DaftarPengajuan;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{

    
    public function my()
    {
        $data = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return ApiResponse::success(
            PengajuanResource::collection($data),
            'Pengajuan saya'
        );
    }


    public function histori(Request $request)
    {
        $tahun = $request->tahun;

        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ])
            ->where('user_id', auth()->id());

        if ($tahun) {
            $query->whereYear('date', $tahun);
        }

        $data = $query->latest()->get();

        return ApiResponse::success(
            PengajuanResource::collection($data),
            'Histori pengajuan'
        );
    }
}