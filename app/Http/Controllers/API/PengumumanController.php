<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pengumuman\StorePengumumanRequest;
use App\Http\Requests\Pengumuman\UpdatePengumumanRequest;
use App\Http\Resources\PengumumanResource;
use App\Models\Pengumuman;
use OpenApi\Attributes as OA;

class PengumumanController extends Controller
{
    #[OA\Get(
        path: "/api/pengumuman",
        tags: ["Pengumuman"],
        summary: "List pengumuman",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List pengumuman berhasil diambil"
            )
        ]
    )]
    public function index()
    {
        return ApiResponse::success(
            PengumumanResource::collection(
                Pengumuman::orderBy('create_at', 'desc')->get()
            )
        );
    }

    #[OA\Post(
        path: "/api/pengumuman",
        tags: ["Pengumuman"],
        summary: "Tambah pengumuman",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["judul"],
                    properties: [
                        new OA\Property(property: "judul", type: "string", example: "Pengumuman Libur Nasional"),
                        new OA\Property(property: "deskripsi", type: "string", example: "Libur tanggal 17 Agustus"),
                        new OA\Property(
                            property: "gambar",
                            type: "string",
                            format: "binary",
                            description: "Upload gambar pengumuman"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Pengumuman berhasil dibuat"
            )
        ]
    )]
    public function store(StorePengumumanRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {

            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('pengumuman', $filename, 'public');

            $data['gambar'] = $filename;
        }

        $pengumuman = Pengumuman::create($data);

        return ApiResponse::success(
            new PengumumanResource($pengumuman),
            'Pengumuman berhasil dibuat'
        );
    }

    #[OA\Get(
        path: "/api/pengumuman/{id}",
        tags: ["Pengumuman"],
        summary: "Detail pengumuman",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID pengumuman",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail pengumuman berhasil diambil"
            )
        ]
    )]
    public function show(Pengumuman $pengumuman)
    {
        return ApiResponse::success(
            new PengumumanResource($pengumuman)
        );
    }

    #[OA\Post(
        path: "/api/pengumuman/{id}",
        tags: ["Pengumuman"],
        summary: "Update pengumuman",
        description: "Gunakan POST + multipart/form-data jika update dengan gambar",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "judul", type: "string", example: "Update Pengumuman"),
                        new OA\Property(property: "deskripsi", type: "string", example: "Isi pengumuman terbaru"),
                        new OA\Property(
                            property: "gambar",
                            type: "string",
                            format: "binary"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Pengumuman berhasil diupdate"
            )
        ]
    )]
    public function update(UpdatePengumumanRequest $request, Pengumuman $pengumuman)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {

            if (
                $pengumuman->gambar &&
                file_exists(public_path('storage/pengumuman/' . $pengumuman->gambar))
            ) {

                unlink(public_path('storage/pengumuman/' . $pengumuman->gambar));
            }

            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('pengumuman', $filename, 'public');

            $data['gambar'] = $filename;
        }

        $pengumuman->update($data);

        return ApiResponse::success(
            new PengumumanResource($pengumuman),
            'Pengumuman berhasil diupdate'
        );
    }

    #[OA\Delete(
        path: "/api/pengumuman/{id}",
        tags: ["Pengumuman"],
        summary: "Hapus pengumuman",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Pengumuman berhasil dihapus"
            )
        ]
    )]
    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return ApiResponse::deleted();
    }
}