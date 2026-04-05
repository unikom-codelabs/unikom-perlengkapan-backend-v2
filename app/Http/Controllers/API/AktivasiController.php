<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AktivasiPengajuan\StoreAktivasiPengajuanRequest;
use App\Http\Requests\AktivasiPengajuan\UpdateAktivasiPengajuanRequest;
use App\Http\Resources\AktivasiPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AktivasiController extends Controller
{
    public function index()
    {
        $data = AktivasiPengajuan::latest()->get();

        return ApiResponse::success(
            AktivasiPengajuanResource::collection($data),
            'List periode pengajuan'
        );
    }

    public function store(StoreAktivasiPengajuanRequest $request)
    {
        if ($request->status_aktif) {
            AktivasiPengajuan::where('status_aktif', true)
                ->update(['status_aktif' => false]);
        }

        $data = AktivasiPengajuan::create($request->validated());

        return ApiResponse::success(
            new AktivasiPengajuanResource($data),
            'Periode berhasil dibuat'
        );
    }

    public function show(AktivasiPengajuan $aktivasiPengajuan)
    {
        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Detail periode'
        );
    }

    public function update(UpdateAktivasiPengajuanRequest $request, AktivasiPengajuan $aktivasiPengajuan)
    {
        if ($request->status_aktif) {
            AktivasiPengajuan::where('status_aktif', true)
                ->where('id', '!=', $aktivasiPengajuan->id)
                ->update(['status_aktif' => false]);
        }

        $aktivasiPengajuan->update($request->validated());

        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Periode berhasil diupdate'
        );
    }

    public function destroy(AktivasiPengajuan $aktivasiPengajuan)
    {
        $aktivasiPengajuan->delete();

        return ApiResponse::success(
            null,
            'Periode berhasil dihapus'
        );
    }

    public function activate(AktivasiPengajuan $aktivasiPengajuan)
    {
        AktivasiPengajuan::where('status_aktif', true)
            ->update(['status_aktif' => false]);

        $aktivasiPengajuan->update([
            'status_aktif' => true
        ]);

        return ApiResponse::success(
            new AktivasiPengajuanResource($aktivasiPengajuan),
            'Periode diaktifkan'
        );
    }

    public function current()
    {
        $data = AktivasiPengajuan::where('status_aktif', true)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->first();

        if (!$data) {
            return ApiResponse::error(
                'Tidak ada periode aktif',
                404
            );
        }

        return ApiResponse::success(
            new AktivasiPengajuanResource($data),
            'Periode aktif ditemukan'
        );
    }
}
