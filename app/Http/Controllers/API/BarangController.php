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
        return ApiResponse::success(
            BarangResource::collection(
                Barang::with('vendor')->latest()->get()
            )
        );
    }

    public function store(StoreBarangRequest $request)
    {
        $barang = Barang::create($request->validated());

        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    public function show(Barang $barang)
    {
        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $barang->update($request->validated());

        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return ApiResponse::success(null, 'deleted');
    }
}
