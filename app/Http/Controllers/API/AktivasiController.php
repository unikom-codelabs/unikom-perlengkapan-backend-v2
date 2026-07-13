<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AktivasiPengajuan\StoreAktivasiPengajuanRequest;
use App\Http\Requests\AktivasiPengajuan\UpdateAktivasiPengajuanRequest;
use App\Http\Resources\AktivasiPengajuanResource;
use App\Models\AktivasiPengajuan;
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
}
