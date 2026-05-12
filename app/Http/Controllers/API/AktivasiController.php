<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AktivasiPengajuan\StoreAktivasiPengajuanRequest;
use App\Http\Requests\AktivasiPengajuan\UpdateAktivasiPengajuanRequest;
use App\Http\Resources\AktivasiPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Models\DaftarPengajuan;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AktivasiController extends Controller
{
    #[OA\Get(
        path: '/api/aktivasi-pengajuan',
        tags: ['Aktivasi Pengajuan'],
        summary: 'List semua aktivasi pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'tipe',
                in: 'query',
                description: 'Filter tipe pengajuan (kelas, ujian, tahunan)',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'kelas')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil ambil data'),
        ]
    )]
    public function index(Request $request)
    {

        $query = AktivasiPengajuan::with('pengajuan');

        if ($request->tipe) {

            $query->whereHas('pengajuan', function ($q) use ($request) {

                $q->where('tipe', $request->tipe);
            });
        }

        return AktivasiPengajuanResource::collection(

            $query->latest()->get()
        );
    }

    #[OA\Post(
        path: '/api/aktivasi-pengajuan',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Tambah aktivasi',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_pengajuan', 'aktif_mulai', 'aktif_selesai', 'tipe', 'tahun_akademik'],

                properties: [

                    new OA\Property(property: 'id_pengajuan', type: 'integer', example: 1),

                    new OA\Property(property: 'aktif_mulai', type: 'string', example: '2026-01-01'),

                    new OA\Property(property: 'aktif_selesai', type: 'string', example: '2026-12-31'),

                    new OA\Property(property: 'tipe', type: 'string', example: 'rutin'),

                    new OA\Property(property: 'tahun_akademik', type: 'string', example: '2026/2027'),

                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Berhasil membuat aktivasi'),
        ]
    )]
    public function store(StoreAktivasiPengajuanRequest $request)
    {

        $aktivasi = AktivasiPengajuan::create(

            $request->validated()
        );

        return new AktivasiPengajuanResource(

            $aktivasi->load('pengajuan')
        );
    }

    #[OA\Get(
        path: '/api/aktivasi-pengajuan/{id}',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Detail aktivasi',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(

                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail ditemukan'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show(AktivasiPengajuan $aktivasiPengajuan)
    {

        return new AktivasiPengajuanResource(

            $aktivasiPengajuan->load('pengajuan')
        );
    }

    #[OA\Put(
        path: '/api/aktivasi-pengajuan/{id}',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Update aktivasi',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(

                properties: [

                    new OA\Property(property: 'id_pengajuan', type: 'integer'),

                    new OA\Property(property: 'aktif_mulai', type: 'string'),

                    new OA\Property(property: 'aktif_selesai', type: 'string'),

                    new OA\Property(property: 'tipe', type: 'string'),

                    new OA\Property(property: 'tahun_akademik', type: 'string'),

                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Berhasil update'),
        ]
    )]
    public function update(
        UpdateAktivasiPengajuanRequest $request,
        AktivasiPengajuan $aktivasiPengajuan
    ) {

        $aktivasiPengajuan->update(

            $request->validated()
        );

        return new AktivasiPengajuanResource(

            $aktivasiPengajuan->load('pengajuan')
        );
    }

    #[OA\Delete(
        path: '/api/aktivasi-pengajuan/{id}',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Hapus aktivasi',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil hapus'),
        ]
    )]
    public function destroy(AktivasiPengajuan $aktivasiPengajuan)
    {

        $aktivasiPengajuan->delete();

        return response()->json([

            'message' => 'Aktivasi berhasil dihapus',
        ]);
    }

    #[OA\Get(
        path: '/api/periode-aktif',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Ambil periode aktif saat ini',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(
                name: 'tipe',
                in: 'query',
                description: 'kelas | ujian | tahunan',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'ujian')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Periode aktif ditemukan'),
            new OA\Response(response: 404, description: 'Tidak ada periode aktif'),
        ]
    )]
    public function current(Request $request)
    {

        $query = AktivasiPengajuan::with('pengajuan')

            ->whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now());

        if ($request->tipe) {

            $query->whereHas('pengajuan', function ($q) use ($request) {

                $q->where('tipe', $request->tipe);
            });
        }

        return AktivasiPengajuanResource::collection(

            $query->get()
        );
    }

    #[OA\Patch(
        path: '/api/aktivasi-pengajuan/{id}/activate',
        tags: ['Aktivasi Pengajuan'],
        summary: 'Aktifkan periode',
        security: [['bearerAuth' => []]],
        parameters: [

            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil aktivasi'),
        ]
    )]
    public function activate(AktivasiPengajuan $aktivasiPengajuan)
    {

        return response()->json([

            'message' => 'Periode aktivasi siap digunakan',

            'data' => new AktivasiPengajuanResource(

                $aktivasiPengajuan->load('pengajuan')
            ),
        ]);
    }

    public function summary($id)
    {
        $aktivasi = AktivasiPengajuan::with('pengajuan')
            ->findOrFail($id);

        $totalPengaju = User::count();

        $sudahPengajuan = DaftarPengajuan::where(
            'id_aktivasi',
            $id
        )
            ->distinct('user_id')
            ->count();

        $units = User::with([
            'unit',
            'jabatan',
        ])
            ->get()
            ->map(function ($user) use ($id) {

                $pengajuan = DaftarPengajuan::with([
                    'barang.barang.vendor',
                    'barangLainnya',
                ])
                    ->where('id_aktivasi', $id)
                    ->where('user_id', $user->id)
                    ->first();

                return [

                    // FIX
                    'nama' => $user->username,

                    'unit' => $user->unit?->nama ?? '-',

                    'jabatan' => $user->jabatan?->nama ?? '-',

                    'status' => $pengajuan
                        ? 'Sudah Pengajuan'
                        : 'Belum Pengajuan',

                    'barang' => $pengajuan
                        ? $pengajuan->barang->map(function ($item) {

                            return [

                                // FIX
                                'nama_barang' => $item->barang?->nama_barang ?? '-',

                                // FIX
                                'qty' => $item->jumlah ?? 0,

                                'jumlah_disetujui' => $item->jumlah_disetujui ?? 0,

                                'status' => $item->status ?? '-',

                                'vendor' => $item->barang?->vendor?->nama ?? '-',
                            ];
                        })
                        : [],

                    'barang_lainnya' => $pengajuan
                        ? BarangPengajuanLainnyaResource::collection(
                            $pengajuan->barangLainnya
                        )
                        : [],
                ];
            });

        return ApiResponse::success([
            'aktivasi' => $aktivasi,

            'statistik' => [
                'jumlah_pengajuan_masuk' => $sudahPengajuan,
                'total_pengaju' => $totalPengaju,
            ],

            'detail_pengajuan' => $units,
        ]);
    }
}
