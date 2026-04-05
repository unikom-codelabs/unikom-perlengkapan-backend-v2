<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanRequest;
use App\Http\Requests\StoreBarangPengajuanRequest;
use App\Http\Requests\ApprovalBarangRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\DaftarPengajuan;
use App\Models\BarangPengajuan;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;
use App\Models\BarangPengajuanLainnya;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{


    public function index()
    {

        $query = DaftarPengajuan::with([
            'user.position',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'
        ]);

        if (auth()->user()->role != 'admin') {

            $query->where('user_id', auth()->id());
        }

        $data = $query->latest()->paginate(10);

        return ApiResponse::success(
            PengajuanResource::collection($data)
        );
    }

    public function show($id)
    {

        $data = DaftarPengajuan::with([
            'user.position',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya'

        ])
            ->findOrFail($id);

        return ApiResponse::success(
            new PengajuanResource($data)
        );
    }

    public function store(StorePengajuanRequest $request)
    {

        $data = DaftarPengajuan::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'date' => now()
        ]);
        return ApiResponse::success($data, 'pengajuan dibuat');
    }

    public function uploadSurat(Request $request)
    {
        $request->validate([
            'surat' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ]);

        $file = $request->file('surat')
            ->store('surat', 'public');

        return ApiResponse::success([

            'file' => $file

        ], 'upload berhasil');
    }

    public function tambahBarang(StoreBarangPengajuanRequest $request)
    {
        $data = BarangPengajuan::create(
            $request->validated()
        );

        return ApiResponse::success(
             $data,
            'barang ditambahkan'
        );
    }

    public function tambahBarangLainnya(Request $request)
    {

        $request->validate([
            'daftar_pengajuan_id' => 'required',
            'nama' => 'required',
            'jumlah' => 'required',
            'satuan' => 'required',
            'kategori' => 'required'
        ]);

        $data = BarangPengajuanLainnya::create(
            $request->all()
        );

        return ApiResponse::success(
            $data,
            'barang lainnya ditambahkan'
        );
    }

    public function approval(ApprovalBarangRequest $request, $id) {
        $data = BarangPengajuan::findOrFail($id);
        if ( $request->jumlah_disetujui > $data->jumlah) {
            return ApiResponse::error(
                'jumlah disetujui melebihi permintaan'
            );
        }
        
        $data->update([
            'jumlah_disetujui' => $request->jumlah_disetujui,
            'status' => true
        ]);

        return ApiResponse::success(
            $data,
            'disetujui'
        );
    }
}
