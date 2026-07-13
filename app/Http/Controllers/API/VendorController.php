<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Helpers\ApiResponse;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->paginate(10);

        return ApiResponse::success(
            VendorResource::collection($vendors),
            'List vendor'
        );
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());

        return ApiResponse::success(
            new VendorResource($vendor),
            'Vendor berhasil dibuat'
        );
    }

    public function show(Vendor $vendor)
    {
        return ApiResponse::success(
            new VendorResource($vendor),
            'Detail vendor'
        );
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return ApiResponse::success(
            new VendorResource($vendor),
            'Vendor berhasil diupdate'
        );
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->barang()->exists()) {
            return ApiResponse::error(
                'Vendor tidak bisa dihapus karena masih digunakan barang',
                422
            );
        }

        $vendor->delete();

        return ApiResponse::success(
            null,
            'Vendor berhasil dihapus'
        );
    }
}
