<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Http\Requests\Barang\StoreBarangRequest;
use App\Http\Requests\Barang\UpdateBarangRequest;
use App\Http\Resources\BarangResource;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Cache;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Cache::remember('barang_index', 300, function () {
            return Barang::with('vendor')->latest()->get();
        });

        return ApiResponse::success(
            BarangResource::collection($barang)
        );
    }

    public function store(StoreBarangRequest $request)
    {
        $barang = Barang::create($request->validated());
        Cache::forget('barang_index');

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
        Cache::forget('barang_index');

        return ApiResponse::success(
            new BarangResource($barang->load('vendor'))
        );
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();
        Cache::forget('barang_index');

        return ApiResponse::success(null, 'deleted');
    }
}
