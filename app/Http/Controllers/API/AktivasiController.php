<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AktivasiPengajuan\StoreAktivasiPengajuanRequest;
use App\Http\Requests\AktivasiPengajuan\UpdateAktivasiPengajuanRequest;
use App\Http\Resources\AktivasiPengajuanResource;
use App\Http\Resources\BarangPengajuanLainnyaResource;
use App\Models\AktivasiPengajuan;
use App\Models\DaftarPengajuan;
use App\Models\User;
use Illuminate\Http\Request;

class AktivasiController extends Controller
{
    public function index(Request $request)
    {

        $query = AktivasiPengajuan::with('pengajuan');

        if ($request->tipe) {

            $query->whereHas('pengajuan', function ($q) use ($request) {

                $q->where('tipe', $request->tipe);
            });
        }

        return AktivasiPengajuanResource::collection(

            $query->latest()->get()
        );
    }

    public function store(StoreAktivasiPengajuanRequest $request)
    {

        $aktivasi = AktivasiPengajuan::create(

            $request->validated()
        );

        return new AktivasiPengajuanResource(

            $aktivasi->load('pengajuan')
        );
    }

    public function show(AktivasiPengajuan $aktivasiPengajuan)
    {

        return new AktivasiPengajuanResource(

            $aktivasiPengajuan->load('pengajuan')
        );
    }

    public function update(
        UpdateAktivasiPengajuanRequest $request,
        AktivasiPengajuan $aktivasiPengajuan
    ) {

        $aktivasiPengajuan->update(

            $request->validated()
        );

        return new AktivasiPengajuanResource(

            $aktivasiPengajuan->load('pengajuan')
        );
    }

    public function destroy(AktivasiPengajuan $aktivasiPengajuan)
    {

        $aktivasiPengajuan->delete();

        return response()->json([

            'message' => 'Aktivasi berhasil dihapus',
        ]);
    }

    public function current(Request $request)
    {

        $query = AktivasiPengajuan::with('pengajuan')

            ->whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now());

        if ($request->tipe) {

            $query->whereHas('pengajuan', function ($q) use ($request) {

                $q->where('tipe', $request->tipe);
            });
        }

        return AktivasiPengajuanResource::collection(

            $query->get()
        );
    }

    public function activate(AktivasiPengajuan $aktivasiPengajuan)
    {

        return response()->json([

            'message' => 'Periode aktivasi siap digunakan',

            'data' => new AktivasiPengajuanResource(

                $aktivasiPengajuan->load('pengajuan')
            ),
        ]);
    }

    public function summary($id)
    {
        $aktivasi = AktivasiPengajuan::with('pengajuan')
            ->findOrFail($id);

        $totalPengaju = User::count();

        $sudahPengajuan = DaftarPengajuan::where(
            'id_aktivasi',
            $id
        )
            ->distinct('user_id')
            ->count();

        $units = User::with([
            'unit',
            'jabatan',
        ])
            ->get()
            ->map(function ($user) use ($id) {

                $pengajuan = DaftarPengajuan::with([
                    'barang.barang.vendor',
                    'barangLainnya',
                ])
                    ->where('id_aktivasi', $id)
                    ->where('user_id', $user->id)
                    ->first();

                return [

                    
                    'nama' => $user->username,

                    'unit' => $user->unit?->nama ?? '-',

                    'jabatan' => $user->jabatan?->nama ?? '-',

                    'status' => $pengajuan
                        ? 'Sudah Pengajuan'
                        : 'Belum Pengajuan',

                    'barang' => $pengajuan
                        ? $pengajuan->barang->map(function ($item) {

                            return [

                                
                                'nama_barang' => $item->barang?->nama ?? '-',

                                
                                'qty' => $item->jumlah ?? 0,

                                'jumlah_disetujui' => $item->jumlah_disetujui ?? 0,

                                'status' => $item->status ?? '-',

                                'vendor' => $item->barang?->vendor?->nama ?? '-',
                            ];
                        })
                        : [],

                    'barang_lainnya' => $pengajuan
                        ? BarangPengajuanLainnyaResource::collection(
                            $pengajuan->barangLainnya
                        )
                        : [],
                ];
            });

        return ApiResponse::success([
            'aktivasi' => $aktivasi,

            'statistik' => [
                'jumlah_pengajuan_masuk' => $sudahPengajuan,
                'total_pengaju' => $totalPengaju,
            ],

            'detail_pengajuan' => $units,
        ]);
    }
}
