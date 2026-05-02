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
    #[OA\Post(
        path: '/api/barang-pengajuan-lainnya',
        tags: ['Barang Pengajuan Lainnya'],
        summary: 'Tambah barang manual',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'jumlah'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Mouse Wireless'),
                    new OA\Property(property: 'jumlah', type: 'integer', example: 3),
                    new OA\Property(property: 'keterangan', type: 'string', example: 'Untuk kebutuhan divisi IT'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Barang manual berhasil ditambahkan'
            ),
        ]
    )]
    public function store(StoreBarangPengajuanLainnyaRequest $request)
    {
        $data = $request->validated();

        $data['jumlah_disetujui'] = 0;
        $data['status'] = false;

        $barang = BarangPengajuanLainnya::create($data);

        return ApiResponse::success(
            new BarangPengajuanLainnyaResource($barang),
            'Barang manual berhasil ditambahkan'
        );
    }

    #[OA\Put(
        path: '/api/barang-pengajuan-lainnya/{id}',
        tags: ['Barang Pengajuan Lainnya'],
        summary: 'Update barang manual',
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
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Keyboard Mechanical'),
                    new OA\Property(property: 'jumlah', type: 'integer', example: 5),
                    new OA\Property(property: 'keterangan', type: 'string', example: 'Update kebutuhan divisi'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang manual berhasil diupdate'
            ),
        ]
    )]
    public function update(
        UpdateBarangPengajuanLainnyaRequest $request,
        BarangPengajuanLainnya $barangPengajuanLainnya
    ) {
        $barangPengajuanLainnya->update(
            $request->validated()
        );

        return ApiResponse::success(
            new BarangPengajuanLainnyaResource($barangPengajuanLainnya)
        );
    }

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

    #[OA\Delete(
        path: '/api/barang-pengajuan-lainnya/{id}',
        tags: ['Barang Pengajuan Lainnya'],
        summary: 'Hapus barang manual',
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
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang manual berhasil dihapus'
            ),
        ]
    )]
    public function destroy(
        BarangPengajuanLainnya $barangPengajuanLainnya
    ) {

        $barangPengajuanLainnya->delete();

        return ApiResponse::deleted();
    }
}
