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
        $request->validate([
            'tahun' => 'required',
            'id_aktivasi' => 'required|integer',
            'tipe' => 'nullable|in:rutin,nonrutin',
            'kategori_atk' => 'nullable|in:tahunan,kelas,ujian',
        ]);

        $tahun = $request->tahun;
        $aktivasi = $request->id_aktivasi;
        $tipe = $request->tipe;
        $kategoriAtk = $request->kategori_atk;

        $query = DaftarPengajuan::with([
            'aktivasi',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
        ])
            ->where('id_aktivasi', $aktivasi)
            ->whereYear('created_at', $tahun);

        /*
        |--------------------------------------------------------------------------
        | Filter Tipe Aktivasi
        |--------------------------------------------------------------------------
        */
        if ($tipe) {

            $query->whereHas('aktivasi', function ($q) use ($tipe) {

                $q->where('tipe', $tipe);

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kategori ATK
        |--------------------------------------------------------------------------
        */
        if ($kategoriAtk) {
            $query->whereHas('aktivasi.pengajuan', function ($q) use ($kategoriAtk) {
                $q->where('tipe', $kategoriAtk);
            });
        }

        $pengajuan = $query
            ->latest()
            ->get();

        $barangPengajuan = collect();

        $barangPengajuanLainnya = collect();

        /*
        |--------------------------------------------------------------------------
        | Barang Pengajuan
        |--------------------------------------------------------------------------
        */
        foreach ($pengajuan as $data) {

            foreach ($data->barang as $barang) {

                $barangPengajuan->push([
                    'nama_barang' => $barang->barang->nama ?? '-',
                    'satuan' => $barang->barang->satuan ?? '-',
                    'jumlah' => $barang->jumlah ?? 0,
                    'harga' => $barang->barang->harga ?? 0,
                    'subtotal' => ($barang->jumlah ?? 0) * ($barang->barang->harga ?? 0),
                    'vendor' => $barang->barang->vendor->nama ?? '-',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Barang Pengajuan Lainnya
            |--------------------------------------------------------------------------
            */
            foreach ($data->barangLainnya as $barangLainnya) {

                $barangPengajuanLainnya->push([
                    'nama_barang' => $barangLainnya->nama ?? '-',
                    'kategori' => $barangLainnya->kategori ?? '-',
                    'satuan' => $barangLainnya->satuan ?? '-',
                    'jumlah' => $barangLainnya->jumlah ?? 0,
                    'harga' => 0,
                    'subtotal' => 0,
                    'vendor' => '-',
                ]);
            }
        }

        $grouped = $items
            ->groupBy(function ($item) {

                return strtolower(trim($item['nama_barang']));

            })
            ->map(function ($rows) {

                $harga = $rows->first()['harga'];
                $jumlah = $rows->sum('jumlah');

                return [
                    'nama_barang' => $rows->first()['nama_barang'],
                    'satuan' => $rows->first()['satuan'],
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $jumlah * $harga,
                    'vendor' => $rows->first()['vendor'],
                ];
            })
            ->values();

        return ApiResponse::success([
            'tahun' => $tahun,
            'id_aktivasi' => $aktivasi,

            'total_harga' => $barangPengajuan->sum('subtotal') +

                $barangPengajuanLainnya->sum('subtotal'),

            'barang_pengajuan' => $barangPengajuan,

            'barang_pengajuan_lainnya' => $barangPengajuanLainnya,
        ]);
    }
}
