<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengajuanResource;
use App\Models\DaftarPengajuan;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PengajuanController extends Controller
{

    #[OA\Get(
        path: "/api/pengajuan",
        tags: ["Pengajuan"],
        summary: "List pengajuan",
        security: [["bearerAuth" => []]]
    )]
    public function index()
    {
        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ]);

        // 🔒 user hanya lihat miliknya
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $data = $query->latest()->paginate(10);

        return ApiResponse::success(
            PengajuanResource::collection($data),
            'List pengajuan'
        );
    }


    #[OA\Get(
        path: "/api/pengajuan/{id}",
        tags: ["Pengajuan"],
        summary: "Detail pengajuan",
        security: [["bearerAuth" => []]]
    )]
    public function show($id)
    {
        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ])->where('id', $id);

        // 🔒 proteksi akses
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $data = $query->firstOrFail();

        return ApiResponse::success(
            new PengajuanResource($data),
            'Detail pengajuan'
        );
    }


    #[OA\Get(
        path: "/api/pengajuan/my",
        tags: ["Pengajuan"],
        summary: "Pengajuan milik user login",
        security: [["bearerAuth" => []]]
    )]
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


    #[OA\Get(
        path: "/api/histori-pengajuan",
        tags: ["Pengajuan"],
        summary: "Histori pengajuan berdasarkan tahun",
        security: [["bearerAuth" => []]]
    )]
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