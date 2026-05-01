<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Http\Requests\Barang\StoreBarangRequest;
use App\Http\Requests\Barang\UpdateBarangRequest;
use App\Http\Resources\BarangResource;
use App\Helpers\ApiResponse;
use OpenApi\Attributes as OA;

class BarangController extends Controller
{
    #[OA\Get(
        path: "/api/barang",
        tags: ["Barang"],
        summary: "List barang",
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function index()
    {
        return ApiResponse::success(
            BarangResource::collection(
                Barang::with('vendor')->latest()->get()
            )
        );
    }

    #[OA\Post(
        path: "/api/barang",
        tags: ["Barang"],
        summary: "Create barang",
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreBarangRequest $request)
    {
        $barang = Barang::create($request->validated());

        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    #[OA\Get(
        path: "/api/barang/{id}",
        tags: ["Barang"],
        summary: "Detail barang",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function show(Barang $barang)
    {
        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    #[OA\Put(
        path: "/api/barang/{id}",
        tags: ["Barang"],
        summary: "Update barang",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $barang->update($request->validated());

        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    #[OA\Delete(
        path: "/api/barang/{id}",
        tags: ["Barang"],
        summary: "Delete barang",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return ApiResponse::success(null, 'deleted');
    }
}
