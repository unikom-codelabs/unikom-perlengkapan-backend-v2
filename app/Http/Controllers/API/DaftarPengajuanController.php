<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DaftarPengajuan\StoreDaftarPengajuanRequest;
use App\Http\Requests\DaftarPengajuan\StoreFullDaftarPengajuanRequest;
use App\Http\Requests\DaftarPengajuan\UpdateDaftarPengajuanRequest;
use App\Http\Resources\DaftarPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Models\DaftarPengajuan;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class DaftarPengajuanController extends Controller
{
    public function storeFull(StoreFullDaftarPengajuanRequest $request)
    {
        $aktivasiIds = AktivasiPengajuan::whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now())
            ->pluck('id');

        if ($aktivasiIds->isEmpty()) {
            return ApiResponse::error('Tidak ada periode pengajuan aktif', 422);
        }

        $sudahMengajukan = DaftarPengajuan::where('user_id', auth()->id())
            ->whereIn('id_aktivasi', $aktivasiIds)
            ->exists();

        if ($sudahMengajukan) {
            return ApiResponse::error(
                'Anda sudah melakukan pengajuan pada periode ini',
                422
            );
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {

            $idAktivasi = $aktivasiIds->first();

            $pengajuan = DaftarPengajuan::create([
                'id_aktivasi' => $idAktivasi,
                'user_id' => auth()->id(),
                'date' => now(),
                'surat_pengajuan' => $request->hasFile('surat_pengajuan')
                    ? $request->file('surat_pengajuan')->store('surat_pengajuan', 'public')
                    : '',
            ]);

            foreach ($data['barang'] ?? [] as $item) {
                if ($item['jumlah'] > 0) {
                    $pengajuan->barang()->create([
                        'id_barang' => $item['id_barang'],
                        'jumlah' => $item['jumlah'],
                        'jumlah_disetujui' => 0,
                        'status' => false,
                    ]);
                }
            }

            foreach ($data['barang_lainnya'] ?? [] as $item) {
                $pengajuan->barangLainnya()->create([
                    'nama' => $item['nama'],
                    'jumlah' => $item['jumlah'],
                    'kategori' => $item['kategori'],
                    'satuan' => $item['satuan'],
                    'jumlah_disetujui' => 0,
                    'status' => false,
                ]);
            }

            DB::commit();

            return ApiResponse::success(
                new DaftarPengajuanResource(
                    $pengajuan->load('barang.barang', 'barangLainnya')
                ),
                'Pengajuan berhasil dibuat'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/daftar-pengajuan',
        tags: ['Daftar Pengajuan'],
        summary: 'List semua pengajuan',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List pengajuan berhasil diambil'
            ),
        ]
    )]
    public function index()
    {
        return ApiResponse::success(

            DaftarPengajuanResource::collection(

                DaftarPengajuan::with([
                    'user',
                    'aktivasi',
                    'barang.barang',
                    'barangLainnya',
                ])->latest()->get()

            )

        );
    }

    #[OA\Post(
        path: '/api/daftar-pengajuan',
        tags: ['Daftar Pengajuan'],
        summary: 'Buat pengajuan baru',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['aktivasi_pengajuan_id'],
                    properties: [
                        new OA\Property(property: 'aktivasi_pengajuan_id', type: 'integer', example: 1),
                        new OA\Property(property: 'surat_pengajuan', type: 'string', format: 'binary', description: 'File surat pengajuan'),
                        new OA\Property(property: 'catatan', type: 'string', example: 'Pengajuan kebutuhan ATK'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pengajuan berhasil dibuat'
            ),
            new OA\Response(
                response: 422,
                description: 'Periode pengajuan tidak aktif'
            ),
        ]
    )]
    public function store(StoreDaftarPengajuanRequest $request)
    {
        $data = $request->validated();

        $aktivasi = AktivasiPengajuan::where('id', $data['aktivasi_pengajuan_id'])
            ->whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now())
            ->first();

        if (! $aktivasi) {
            return ApiResponse::error(
                'Periode pengajuan tidak aktif',
                422
            );
        }

        $data['user_id'] = auth()->id();

        $data['id_aktivasi'] = $data['aktivasi_pengajuan_id'];

        unset($data['aktivasi_pengajuan_id']);

        if ($request->hasFile('surat_pengajuan')) {

            $data['surat_pengajuan'] =
                $request->file('surat_pengajuan')
                    ->store('surat_pengajuan', 'public');
        }

        $pengajuan = DaftarPengajuan::create($data);

        $pengajuan->load([
            'user',
            'aktivasi',
            'barang',
            'barangLainnya',
        ]);

        return ApiResponse::success(
            new DaftarPengajuanResource($pengajuan),
            'Pengajuan berhasil dibuat'
        );
    }

    #[OA\Get(
        path: '/api/daftar-pengajuan/{id}',
        tags: ['Daftar Pengajuan'],
        summary: 'Detail pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail pengajuan berhasil diambil'
            ),
        ]
    )]
    public function show(DaftarPengajuan $daftarPengajuan)
    {

        return ApiResponse::success(

            new DaftarPengajuanResource(

                $daftarPengajuan->load([
                    'user',
                    'aktivasi',
                    'barang.barang',
                    'barangLainnya',
                ])

            )

        );
    }

    #[OA\Put(
        path: '/api/daftar-pengajuan/{id}',
        tags: ['Daftar Pengajuan'],
        summary: 'Update pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'catatan', type: 'string', example: 'Update catatan pengajuan'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pengajuan berhasil diupdate'
            ),
        ]
    )]
    public function update(
        UpdateDaftarPengajuanRequest $request,
        DaftarPengajuan $daftarPengajuan
    ) {

        $daftarPengajuan->update(

            $request->validated()

        );

        return ApiResponse::success(

            new DaftarPengajuanResource($daftarPengajuan)

        );
    }

    #[OA\Delete(
        path: '/api/daftar-pengajuan/{id}',
        tags: ['Daftar Pengajuan'],
        summary: 'Hapus pengajuan',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID pengajuan',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pengajuan berhasil dihapus'
            ),
        ]
    )]
    public function destroy(DaftarPengajuan $daftarPengajuan)
    {

        $daftarPengajuan->delete();

        return ApiResponse::deleted();
    }
}
