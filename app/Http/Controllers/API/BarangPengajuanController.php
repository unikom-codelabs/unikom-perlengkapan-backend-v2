<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BarangPengajuan\StoreBarangPengajuanRequest;
use App\Http\Requests\BarangPengajuan\UpdateBarangPengajuanRequest;
use App\Http\Resources\BarangPengajuanResource;
use App\Models\BarangPengajuan;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BarangPengajuanController extends Controller
{
    
    public function approve(Request $request, BarangPengajuan $barangPengajuan)
    {
        $validated = $request->validate([

            'jumlah_disetujui' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => ['required', 'boolean'],
        ]);

        $barangPengajuan->update([

            'jumlah_disetujui' => $validated['jumlah_disetujui'],

            'status' => $validated['status'],

        ]);

        return ApiResponse::success(
            new BarangPengajuanResource($barangPengajuan->load('barang'))
        );
    }
}
