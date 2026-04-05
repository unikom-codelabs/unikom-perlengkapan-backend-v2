<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Http\Requests\Barang\StoreBarangRequest;
use App\Http\Requests\Barang\UpdateBarangRequest;
use App\Http\Resources\BarangResource;
use App\Helpers\ApiResponse;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::with('vendor')
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            BarangResource::collection($barang),
            'List barang'
        );
    }

    public function store(StoreBarangRequest $request)
    {
        $barang = Barang::create(
            $request->validated()
        );

        return ApiResponse::success(
            new BarangResource(
                $barang->load('vendor')
            ),
            'Barang berhasil dibuat'
        );
    }

    public function show(Barang $barang)
    {
        return ApiResponse::success(
            new BarangResource(
                $barang->load('vendor')
            ),
            'Detail barang'
        );
    }

    public function update(
        UpdateBarangRequest $request,
        Barang $barang
    ) {

        $barang->update(
            $request->validated()
        );

        return ApiResponse::success(
            new BarangResource(
                $barang->load('vendor')
            ),
            'Barang berhasil diupdate'
        );
    }

    public function destroy(Barang $barang)
    {
        if ($barang->barangPengajuan()->exists()) {

            return ApiResponse::error(
                'Barang tidak bisa dihapus karena sudah pernah diajukan',
                422
            );
        }

        $barang->delete();

        return ApiResponse::success(
            null,
            'Barang berhasil dihapus'
        );
    }
}