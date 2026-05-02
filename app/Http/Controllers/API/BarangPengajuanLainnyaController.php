<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BarangPengajuanLainnya\StoreBarangPengajuanLainnyaRequest;
use App\Http\Requests\BarangPengajuanLainnya\UpdateBarangPengajuanLainnyaRequest;
use App\Http\Resources\BarangPengajuanLainnyaResource;
use App\Models\BarangPengajuanLainnya;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BarangPengajuanLainnyaController extends Controller
{

    #[OA\Patch(
        path: '/api/barang-pengajuan-lainnya/{id}/approve',
        tags: ['Barang Pengajuan Lainnya'],
        summary: 'Approve barang manual',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID Barang Pengajuan Lainnya',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['jumlah_disetujui', 'status'],
                properties: [
                    new OA\Property(property: 'jumlah_disetujui', type: 'integer', example: 2),
                    new OA\Property(property: 'status', type: 'integer', example: 1, description: '1=approve, 2=reject'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang manual berhasil di approve'
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi gagal'
            ),
        ]
    )]
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
