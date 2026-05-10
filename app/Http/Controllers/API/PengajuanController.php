<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PengajuanResource;
use App\Models\DaftarPengajuan;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PengajuanController extends Controller
{
    #[OA\Get(
        path: '/api/pengajuan',
        tags: ['Pengajuan'],
        summary: 'List pengajuan',
        security: [['bearerAuth' => []]]
    )]
    public function index()
    {
        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
        ]);

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
        path: '/api/pengajuan/{id}',
        tags: ['Pengajuan'],
        summary: 'Detail pengajuan',
        security: [['bearerAuth' => []]]
    )]
    public function show($id)
    {
        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
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
        path: '/api/pengajuan/my',
        tags: ['Pengajuan'],
        summary: 'Pengajuan milik user login',
        security: [['bearerAuth' => []]]
    )]
    public function my()
    {
        $data = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
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
        path: '/api/histori-pengajuan',
        tags: ['Pengajuan'],
        summary: 'Histori pengajuan berdasarkan aktivasi',
        security: [['bearerAuth' => []]]
    )]
    public function histori(Request $request)
    {
        $aktivasi = $request->id_aktivasi;
        $jabatan = $request->jabatan_id;
        $bagian = $request->bagian_id;

        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
        ]);

        // Filter Aktivasi
        if ($aktivasi) {
            $query->where('id_aktivasi', $aktivasi);
        }

        // Filter Jabatan
        if ($jabatan) {
            $query->whereHas('user.jabatan', function ($q) use ($jabatan) {
                $q->where('id', $jabatan);
            });
        }

        // Filter Unit / Bagian
        if ($bagian) {
            $query->whereHas('user.unit', function ($q) use ($bagian) {
                $q->where('id', $bagian);
            });
        }

        $data = $query->latest()->get();

        return ApiResponse::success(
            PengajuanResource::collection($data),
            'Histori pengajuan'
        );
    }

    #[OA\Get(
        path: '/api/histori-pengajuan/my',
        tags: ['Pengajuan'],
        summary: 'Histori pengajuan milik user login',
        security: [['bearerAuth' => []]]
    )]
    public function historiMy(Request $request)
    {
        $tahun = $request->tahun;

        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang',
            'barang.barang.vendor',
            'barangLainnya',
        ])
            ->where('user_id', auth()->id());

        // Filter tahun
        if ($tahun) {
            $query->whereYear('date', $tahun);
        }

        $data = $query->latest()->get();

        return ApiResponse::success(
            PengajuanResource::collection($data),
            'Histori pengajuan saya'
        );
    }
}
