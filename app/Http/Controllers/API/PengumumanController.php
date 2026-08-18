<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pengumuman\StorePengumumanRequest;
use App\Http\Requests\Pengumuman\UpdatePengumumanRequest;
use App\Http\Resources\PengumumanResource;
use App\Models\Pengumuman;

class PengumumanController extends Controller
{
    public function index()
    {
        return ApiResponse::success(
            PengumumanResource::collection(
                Pengumuman::orderBy('create_at', 'desc')->get()
            )
        );
    }

    public function store(StorePengumumanRequest $request)
    {
        $data = $request->validated();

        
        $data['judul'] = HtmlSanitizer::stripAll($data['judul'] ?? '');
        $data['teks']  = HtmlSanitizer::sanitize($data['teks'] ?? '');

        if ($request->hasFile('gambar')) {

            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('pengumuman', $filename, 'public');

            $data['gambar'] = $filename;
        }

        $pengumuman = Pengumuman::create($data);

        return ApiResponse::success(
            new PengumumanResource($pengumuman),
            'Pengumuman berhasil dibuat'
        );
    }

    public function show(Pengumuman $pengumuman)
    {
        return ApiResponse::success(
            new PengumumanResource($pengumuman)
        );
    }

    public function update(UpdatePengumumanRequest $request, Pengumuman $pengumuman)
    {
        $data = $request->validated();

        
        if (array_key_exists('judul', $data)) {
            $data['judul'] = HtmlSanitizer::stripAll($data['judul']);
        }
        if (array_key_exists('teks', $data)) {
            $data['teks'] = HtmlSanitizer::sanitize($data['teks']);
        }

        if ($request->hasFile('gambar')) {

            if (
                $pengumuman->gambar &&
                file_exists(public_path('storage/pengumuman/' . $pengumuman->gambar))
            ) {

                unlink(public_path('storage/pengumuman/' . $pengumuman->gambar));
            }

            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('pengumuman', $filename, 'public');

            $data['gambar'] = $filename;
        }

        $pengumuman->update($data);

        return ApiResponse::success(
            new PengumumanResource($pengumuman),
            'Pengumuman berhasil diupdate'
        );
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return ApiResponse::deleted();
    }
}