<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DaftarPengajuan\StoreDaftarPengajuanRequest;
use App\Http\Requests\DaftarPengajuan\UpdateDaftarPengajuanRequest;
use App\Http\Resources\DaftarPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Models\DaftarPengajuan;
use Illuminate\Http\Request;

class DaftarPengajuanController extends Controller
{

    public function index()
    {
        return ApiResponse::success(

            DaftarPengajuanResource::collection(

                DaftarPengajuan::with([
                    'user',
                    'aktivasi',
                    'barang.barang',
                    'barangLainnya'
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

        if (!$aktivasi) {
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
            'barangLainnya'
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
                    'barangLainnya'
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
