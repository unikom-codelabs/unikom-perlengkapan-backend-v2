<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DaftarPengajuan;
use Illuminate\Http\Request;

class CetakBerkasController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->tahun;
        $aktivasi = $request->id_aktivasi;

        $query = DaftarPengajuan::with([
            'aktivasi.pengajuan',
            'barang.barang.vendor',
        ]);

        if ($tahun) {

            $query->whereHas(
                'aktivasi',
                function ($q) use ($tahun) {

                    $q->where('tahun_akademik', $tahun);

                }
            );
        }

        if ($aktivasi) {

            $query->where(
                'id_aktivasi',
                $aktivasi
            );
        }

        $pengajuan = $query->get();

        $items = collect();

        foreach ($pengajuan as $data) {

            foreach ($data->barang as $barang) {

                $items->push([
                    'nama_barang' => $barang->barang->nama ?? '-',
                    'satuan'      => $barang->barang->satuan ?? '-',
                    'jumlah'      => $barang->jumlah ?? 0,
                    'harga'       => $barang->barang->harga ?? 0,
                    'vendor'      => $barang->barang->vendor->nama ?? '-',
                ]);
            }
        }

        $grouped = $items
            ->groupBy('nama_barang')
            ->map(function ($rows) {

                $harga = $rows->first()['harga'];

                $jumlah = $rows->sum('jumlah');

                return [
                    'nama_barang' => $rows->first()['nama_barang'],
                    'satuan'      => $rows->first()['satuan'],
                    'jumlah'      => $jumlah,
                    'harga'       => $harga,
                    'subtotal'    => $jumlah * $harga,
                    'vendor'      => $rows->first()['vendor'],
                ];
            })
            ->values();

        return ApiResponse::success([
            'total_harga' => $grouped->sum('subtotal'),
            'items'       => $grouped,
        ]);
    }
}