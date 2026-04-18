<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AktivasiPengajuan\StoreAktivasiPengajuanRequest;
use App\Http\Requests\AktivasiPengajuan\UpdateAktivasiPengajuanRequest;
use App\Http\Resources\AktivasiPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AktivasiController extends Controller
{
    // swager aktivasi
    #[OA\Get(
        path: "/api/aktivasi-pengajuan",
        tags: ["Aktivasi Pengajuan"],
        summary: "List semua periode pengajuan",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List periode pengajuan"
            )
        ]
    )]
    public function index()
    {
        $data = AktivasiPengajuan::latest()->get();

        return ApiResponse::success(
            AktivasiPengajuanResource::collection($data),
            'List periode pengajuan'
        );
    }

    #[OA\Post(
        path: "/api/aktivasi-pengajuan",
        tags: ["Aktivasi Pengajuan"],
        summary: "Buat periode pengajuan",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama_periode", "tanggal_mulai", "tanggal_selesai"],
                properties: [
                    new OA\Property(property: "nama_periode", type: "string", example: "Periode Januari 2026"),
                    new OA\Property(property: "tanggal_mulai", type: "string", format: "date", example: "2026-01-01"),
                    new OA\Property(property: "tanggal_selesai", type: "string", format: "date", example: "2026-01-31"),
                    new OA\Property(property: "status_aktif", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Periode berhasil dibuat")
        ]
    )]

    public function store(StoreAktivasiPengajuanRequest $request)
    {
        if ($request->status_aktif) {
            AktivasiPengajuan::where('status_aktif', true)
                ->update(['status_aktif' => false]);
        }

        $data = AktivasiPengajuan::create($request->validated());

        return ApiResponse::success(
            new AktivasiPengajuanResource($data),
            'Periode berhasil dibuat'
        );
    }

    #[OA\Get(
        path: "/api/aktivasi-pengajuan/{id}",
        tags: ["Aktivasi Pengajuan"],
        summary: "Detail periode pengajuan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID periode",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail periode")
        ]
    )]
    public function show(AktivasiPengajuan $aktivasiPengajuan)
    {
        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Detail periode'
        );
    }

    #[OA\Put(
        path: "/api/aktivasi-pengajuan/{id}",
        tags: ["Aktivasi Pengajuan"],
        summary: "Update periode pengajuan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID periode",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nama_periode", type: "string", example: "Periode Februari 2026"),
                    new OA\Property(property: "tanggal_mulai", type: "string", format: "date", example: "2026-02-01"),
                    new OA\Property(property: "tanggal_selesai", type: "string", format: "date", example: "2026-02-28"),
                    new OA\Property(property: "status_aktif", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Periode berhasil diupdate"),
            new OA\Response(response: 422, description: "Validasi gagal")
        ]
    )]
    
    public function update(UpdateAktivasiPengajuanRequest $request, AktivasiPengajuan $aktivasiPengajuan)
    {
        if ($request->status_aktif) {
            AktivasiPengajuan::where('status_aktif', true)
                ->where('id', '!=', $aktivasiPengajuan->id)
                ->update(['status_aktif' => false]);
        }

        $aktivasiPengajuan->update($request->validated());

        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Periode berhasil diupdate'
        );
    }

    #[OA\Delete(
        path: "/api/aktivasi-pengajuan/{id}",
        tags: ["Aktivasi Pengajuan"],
        summary: "Hapus periode pengajuan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID periode",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Periode berhasil dihapus")
        ]
    )]
    public function destroy(AktivasiPengajuan $aktivasiPengajuan)
    {
        $aktivasiPengajuan->delete();

        return ApiResponse::success(
            null,
            'Periode berhasil dihapus'
        );
    }

    #[OA\Patch(
        path: "/api/aktivasi-pengajuan/{id}/activate",
        tags: ["Aktivasi Pengajuan"],
        summary: "Aktifkan periode pengajuan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID periode",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Periode diaktifkan")
        ]
    )]
    public function activate(AktivasiPengajuan $aktivasiPengajuan)
    {
        AktivasiPengajuan::where('status_aktif', true)
            ->update(['status_aktif' => false]);

        $aktivasiPengajuan->update([
            'status_aktif' => true
        ]);

        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Periode diaktifkan'
        );
    }

    #[OA\Get(
        path: "/api/aktivasi-pengajuan-current",
        tags: ["Aktivasi Pengajuan"],
        summary: "Ambil periode aktif saat ini",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Periode aktif ditemukan"),
            new OA\Response(response: 404, description: "Tidak ada periode aktif")
        ]
    )]
    public function current()
    {
        $data = AktivasiPengajuan::where('status_aktif', true)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->first();

        if (!$data) {
            return ApiResponse::error(
                'Tidak ada periode aktif',
                404
            );
        }

        return ApiResponse::success(
            new AktivasiPengajuanResource($data),
            'Periode aktif ditemukan'
        );
    }
}
