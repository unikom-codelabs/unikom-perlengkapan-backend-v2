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
        /*
        |--------------------------------------------------------------------------
        | Validasi Filter
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'tahun'       => 'required',
            'id_aktivasi' => 'required|integer',
        ]);

        $tahun = $request->tahun;
        $aktivasi = $request->id_aktivasi;

        /*
        |--------------------------------------------------------------------------
        | Query Pengajuan
        |--------------------------------------------------------------------------
        */
        $pengajuan = DaftarPengajuan::with([
            'aktivasi',
            'barang.barang.vendor',
            'barangLainnya',
        ])
        ->where('id_aktivasi', $aktivasi)

        ->whereHas('aktivasi', function ($q) use ($tahun) {

            $q->where('tahun_akademik', $tahun);

        })

        ->get();

        $items = collect();

        /*
        |--------------------------------------------------------------------------
        | Barang Utama
        |--------------------------------------------------------------------------
        */
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

            /*
            |--------------------------------------------------------------------------
            | Barang Lainnya
            |--------------------------------------------------------------------------
            */
            foreach ($data->barangLainnya as $barangLainnya) {

                $items->push([
                    'nama_barang' => $barangLainnya->nama ?? '-',
                    'satuan'      => $barangLainnya->satuan ?? '-',
                    'jumlah'      => $barangLainnya->jumlah ?? 0,
                    'harga'       => 0,
                    'vendor'      => '-',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Group Barang
        |--------------------------------------------------------------------------
        */
        $grouped = $items
            ->groupBy(function ($item) {

                return strtolower($item['nama_barang']);

            })
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
            'tahun'       => $tahun,
            'id_aktivasi' => $aktivasi,
            'total_harga' => $grouped->sum('subtotal'),
            'items'       => $grouped,
        ]);
    }
}