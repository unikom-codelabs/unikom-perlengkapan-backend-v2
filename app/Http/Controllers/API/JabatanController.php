<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jabatan\StoreJabatanRequest;
use App\Http\Requests\Jabatan\UpdateJabatanRequest;
use App\Http\Resources\JabatanResource;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JabatanController extends Controller
{
    #[OA\Get(
        path: "/api/jabatan",
        tags: ["Jabatan"],
        summary: "List jabatan",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List jabatan berhasil diambil"
            )
        ]
    )]
    public function index()
    {
        return ApiResponse::success(
            JabatanResource::collection(
                Jabatan::all()
            )
        );
    }

    #[OA\Post(
        path: "/api/jabatan",
        tags: ["Jabatan"],
        summary: "Tambah jabatan",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "Staff IT")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Jabatan berhasil dibuat"
            )
        ]
    )]
    public function store(StoreJabatanRequest $request)
    {
        $data = Jabatan::create($request->validated());

        return ApiResponse::created(
            new JabatanResource($data)
        );
    }

    #[OA\Get(
        path: "/api/jabatan/{id}",
        tags: ["Jabatan"],
        summary: "Detail jabatan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID jabatan",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail jabatan berhasil diambil"
            )
        ]
    )]
    public function show(Jabatan $jabatan)
    {
        return ApiResponse::success(
            new JabatanResource($jabatan)
        );
    }

    #[OA\Put(
        path: "/api/jabatan/{id}",
        tags: ["Jabatan"],
        summary: "Update jabatan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID jabatan",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "Supervisor IT")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Jabatan berhasil diupdate"
            )
        ]
    )]
    public function update(UpdateJabatanRequest $request, Jabatan $jabatan)
    {
        $jabatan->update($request->validated());

        return ApiResponse::success(
            new JabatanResource($jabatan)
        );
    }

    #[OA\Delete(
        path: "/api/jabatan/{id}",
        tags: ["Jabatan"],
        summary: "Hapus jabatan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID jabatan",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Jabatan berhasil dihapus"
            )
        ]
    )]
    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return ApiResponse::deleted();
    }
}
