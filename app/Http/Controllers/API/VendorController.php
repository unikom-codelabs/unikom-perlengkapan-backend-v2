<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Helpers\ApiResponse;
use OpenApi\Attributes as OA;

class VendorController extends Controller
{
    #[OA\Get(
        path: "/api/vendors",
        tags: ["Vendor"],
        summary: "List vendor",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List vendor berhasil diambil"
            )
        ]
    )]
    public function index()
    {
        $vendors = Vendor::latest()->paginate(10);

        return ApiResponse::success(
            VendorResource::collection($vendors),
            'List vendor'
        );
    }

    #[OA\Get(
        path: "/api/rekap-vendor",
        tags: ["Vendor"],
        summary: "List rekap vendor beserta barang",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List rekap vendor beserta barang berhasil diambil"
            )
        ]
    )]
    public function rekapVendor()
    {
        $vendors = Vendor::with(['barang' => function ($query) {
            $query->withSum('barangPengajuan', 'jumlah_disetujui');
            $query->withSum('barangPengajuan', 'jumlah');
        }])->get();

        return ApiResponse::success(
            VendorResource::collection($vendors),
            'List rekap vendor beserta barang'
        );
    }

    #[OA\Post(
        path: "/api/vendors",
        tags: ["Vendor"],
        summary: "Create vendor",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "PT Sumber Makmur"),
                    new OA\Property(property: "email", type: "string", example: "vendor@email.com"),
                    new OA\Property(property: "phone", type: "string", example: "08123456789"),
                    new OA\Property(property: "address", type: "string", example: "Bandung")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Vendor berhasil dibuat"
            )
        ]
    )]
    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());

        return ApiResponse::success(
            new VendorResource($vendor),
            'Vendor berhasil dibuat'
        );
    }

    #[OA\Get(
        path: "/api/vendors/{id}",
        tags: ["Vendor"],
        summary: "Detail vendor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID Vendor",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail vendor berhasil diambil"
            )
        ]
    )]
    public function show(Vendor $vendor)
    {
        return ApiResponse::success(
            new VendorResource($vendor),
            'Detail vendor'
        );
    }

    #[OA\Put(
        path: "/api/vendors/{id}",
        tags: ["Vendor"],
        summary: "Update vendor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID Vendor",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "PT Sumber Makmur Update"),
                    new OA\Property(property: "email", type: "string", example: "vendorbaru@email.com"),
                    new OA\Property(property: "phone", type: "string", example: "081298765432"),
                    new OA\Property(property: "address", type: "string", example: "Jakarta")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Vendor berhasil diupdate"
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal"
            )
        ]
    )]
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return ApiResponse::success(
            new VendorResource($vendor),
            'Vendor berhasil diupdate'
        );
    }

    #[OA\Delete(
        path: "/api/vendors/{id}",
        tags: ["Vendor"],
        summary: "Delete vendor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID Vendor",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vendor berhasil dihapus"
            ),
            new OA\Response(
                response: 422,
                description: "Vendor masih digunakan barang"
            )
        ]
    )]
    public function destroy(Vendor $vendor)
    {
        if ($vendor->barang()->exists()) {
            return ApiResponse::error(
                'Vendor tidak bisa dihapus karena masih digunakan barang',
                422
            );
        }

        $vendor->delete();

        return ApiResponse::success(
            null,
            'Vendor berhasil dihapus'
        );
    }
}
