<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanRequest;
use App\Http\Requests\StoreBarangPengajuanRequest;
use App\Http\Requests\ApprovalBarangRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\DaftarPengajuan;
use App\Models\BarangPengajuan;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;
use App\Models\BarangPengajuanLainnya;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PengajuanController extends Controller
{

    #[OA\Get(
        path: "/api/pengajuan",
        tags: ["Pengajuan"],
        summary: "List pengajuan (admin lihat semua, user hanya miliknya)",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List pengajuan")
        ]
    )]
    public function index()
    {

        $query = DaftarPengajuan::with([
            'user.position',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ]);

        if (auth()->user()->role != 'admin') {

            $query->where('user_id', auth()->id());
        }

        $data = $query->latest()->paginate(10);

        return ApiResponse::success(
            PengajuanResource::collection($data)
        );
    }


    #[OA\Get(
        path: "/api/pengajuan/{id}",
        tags: ["Pengajuan"],
        summary: "Detail pengajuan",
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
            new OA\Response(response: 200, description: "Detail pengajuan")
        ]
    )]
    public function show($id)
    {

        $data = DaftarPengajuan::with([
            'user.position',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'

        ])
            ->findOrFail($id);

        return ApiResponse::success(
            new PengajuanResource($data)
        );
    }


    #[OA\Get(
        path: "/api/pengajuan/my",
        tags: ["Pengajuan"],
        summary: "List pengajuan milik user login",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List pengajuan milik user")
        ]
    )]
    public function my()
    {
        $data = DaftarPengajuan::with([
            'user.position',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return ApiResponse::success($data);
    }


    #[OA\Post(
        path: "/api/pengajuan",
        tags: ["Pengajuan"],
        summary: "Buat pengajuan",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id_aktivasi", type: "integer", example: 1),
                    new OA\Property(property: "keterangan", type: "string", example: "Pengajuan ATK")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "pengajuan dibuat")
        ]
    )]
    public function store(StorePengajuanRequest $request)
    {

        $data = DaftarPengajuan::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'date' => now()
        ]);

        return ApiResponse::success($data, 'pengajuan dibuat');
    }


    #[OA\Post(
        path: "/api/pengajuan/upload-surat",
        tags: ["Pengajuan"],
        summary: "Upload file surat",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["surat"],
                    properties: [
                        new OA\Property(
                            property: "surat",
                            type: "string",
                            format: "binary",
                            description: "file pdf/doc/docx"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "upload berhasil")
        ]
    )]
    public function uploadSurat(Request $request)
    {
        $request->validate([
            'surat' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ]);

        $file = $request->file('surat')
            ->store('surat', 'public');

        return ApiResponse::success([

            'file' => $file

        ], 'upload berhasil');
    }


    #[OA\Post(
        path: "/api/pengajuan/tambah-barang",
        tags: ["Pengajuan"],
        summary: "Tambah barang ke pengajuan",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "daftar_pengajuan_id", type: "integer", example: 1),
                    new OA\Property(property: "barang_id", type: "integer", example: 2),
                    new OA\Property(property: "jumlah", type: "integer", example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "barang ditambahkan")
        ]
    )]
    public function tambahBarang(StoreBarangPengajuanRequest $request)
    {
        $data = BarangPengajuan::create(
            $request->validated()
        );

        return ApiResponse::success(
            $data,
            'barang ditambahkan'
        );
    }


    #[OA\Post(
        path: "/api/pengajuan/tambah-barang-lainnya",
        tags: ["Pengajuan"],
        summary: "Tambah barang manual ke pengajuan",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "daftar_pengajuan_id", type: "integer", example: 1),
                    new OA\Property(property: "nama", type: "string", example: "Flashdisk"),
                    new OA\Property(property: "jumlah", type: "integer", example: 2),
                    new OA\Property(property: "satuan", type: "string", example: "pcs"),
                    new OA\Property(property: "kategori", type: "string", example: "IT")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "barang lainnya ditambahkan")
        ]
    )]
    public function tambahBarangLainnya(Request $request)
    {

        $request->validate([
            'daftar_pengajuan_id' => 'required',
            'nama' => 'required',
            'jumlah' => 'required',
            'satuan' => 'required',
            'kategori' => 'required'
        ]);

        $data = BarangPengajuanLainnya::create(
            $request->all()
        );

        return ApiResponse::success(
            $data,
            'barang lainnya ditambahkan'
        );
    }


    #[OA\Patch(
        path: "/api/pengajuan/approval/{id}",
        tags: ["Pengajuan"],
        summary: "Approve barang pengajuan",
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
            required: true,
            content: new OA\JsonContent(
                required: ["jumlah_disetujui"],
                properties: [
                    new OA\Property(property: "jumlah_disetujui", type: "integer", example: 3)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "disetujui"),
            new OA\Response(response: 422, description: "jumlah melebihi permintaan")
        ]
    )]
    public function approval(ApprovalBarangRequest $request, $id)
    {
        $data = BarangPengajuan::findOrFail($id);

        if ($request->jumlah_disetujui > $data->jumlah) {

            return ApiResponse::error(
                'jumlah disetujui melebihi permintaan'
            );
        }

        $data->update([
            'jumlah_disetujui' => $request->jumlah_disetujui,
            'status' => true
        ]);

        return ApiResponse::success(
            $data,
            'disetujui'
        );
    }
}
