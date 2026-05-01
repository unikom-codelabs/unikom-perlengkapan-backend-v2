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
    #[OA\Post(
        path: '/api/barang-pengajuan',
        tags: ['Barang Pengajuan'],
        summary: 'Tambah barang pengajuan',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['barang_id', 'jumlah'],
                properties: [
                    new OA\Property(property: 'barang_id', type: 'integer', example: 1),
                    new OA\Property(property: 'jumlah', type: 'integer', example: 10),
                    new OA\Property(property: 'keterangan', type: 'string', example: 'Untuk kebutuhan operasional'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Barang berhasil ditambahkan'
            ),
        ]
    )]
    public function store(StoreBarangPengajuanRequest $request)
    {

        $data = $request->validated();

        $data['jumlah_disetujui'] = 0;

        $data['status'] = 0;

        $barang = BarangPengajuan::create($data);

        return ApiResponse::success(
            new BarangPengajuanResource(
                $barang->load('barang')
            )
        );
    }

    #[OA\Patch(
        path: '/api/barang-pengajuan/{id}/approve',
        tags: ['Barang Pengajuan'],
        summary: 'Approve barang pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID Barang Pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['jumlah_disetujui', 'status'],
                properties: [
                    new OA\Property(property: 'jumlah_disetujui', type: 'integer', example: 5),
                    new OA\Property(property: 'status', type: 'integer', example: 1, description: '1=approve, 2=reject'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang berhasil di approve'
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi gagal'
            ),
        ]
    )]
    public function approve(Request $request, BarangPengajuan $barangPengajuan)
    {
        $validated = $request->validate([

            'jumlah_disetujui' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:1,2',
            ],

        ]);

        $barangPengajuan->update([

            'jumlah_disetujui' => $validated['jumlah_disetujui'],

            'status' => $validated['status'],

        ]);

        return ApiResponse::success(

            $barangPengajuan,

            'Barang berhasil di approve'

        );
    }

    #[OA\Put(
        path: '/api/barang-pengajuan/{id}',
        tags: ['Barang Pengajuan'],
        summary: 'Update barang pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID Barang Pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'barang_id', type: 'integer', example: 1),
                    new OA\Property(property: 'jumlah', type: 'integer', example: 15),
                    new OA\Property(property: 'keterangan', type: 'string', example: 'Update kebutuhan'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang berhasil diupdate'
            ),
        ]
    )]
    public function update(
        UpdateBarangPengajuanRequest $request,
        BarangPengajuan $barangPengajuan
    ) {

        $barangPengajuan->update(

            $request->validated()

        );

        return ApiResponse::success(

            new BarangPengajuanResource(

                $barangPengajuan->load('barang')

            )

        );
    }

    #[OA\Delete(
        path: '/api/barang-pengajuan/{id}',
        tags: ['Barang Pengajuan'],
        summary: 'Hapus barang pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID Barang Pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Barang berhasil dihapus'
            ),
        ]
    )]
    public function destroy(BarangPengajuan $barangPengajuan)
    {

        $barangPengajuan->delete();

        return ApiResponse::deleted();
    }
}
