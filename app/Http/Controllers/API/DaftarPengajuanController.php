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

class DaftarPengajuanController extends Controller
{
    public function storeFull(StoreFullDaftarPengajuanRequest $request)
    {
        $aktivasi = AktivasiPengajuan::whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now())
            ->first();

        if (! $aktivasi) {
            return ApiResponse::error('Tidak ada periode pengajuan aktif', 422);
        }

        $sudahMengajukan = DaftarPengajuan::where('user_id', auth()->id())
            ->where('id_aktivasi', $aktivasi->id)
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

            $pengajuan = DaftarPengajuan::create([
                'id_aktivasi' => $aktivasi->id,
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

    public function destroy(DaftarPengajuan $daftarPengajuan)
    {

        $daftarPengajuan->delete();

        return ApiResponse::deleted();
    }
}
