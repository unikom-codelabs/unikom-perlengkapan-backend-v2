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
        ]);

        $tahun = $request->tahun;
        $aktivasi = $request->id_aktivasi;

        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi',
            'barang.barang.vendor',
            'barangLainnya',
        ]);

        if ($aktivasi) {
            $query->where('id_aktivasi', $aktivasi);
        }

        if ($tahun) {
            $query->whereHas('aktivasi', function ($q) use ($tahun) {
                $q->where('tahun_akademik', $tahun);
            });
        }

        $pengajuan = $query->latest()->get();

        $items = collect();

        foreach ($pengajuan as $data) {

            foreach ($data->barang as $barang) {

                $harga = $barang->barang->harga ?? 0;
                $jumlah = $barang->jumlah ?? 0;

                $items->push([
                    'nama_barang' => $barang->barang->nama ?? '-',
                    'satuan' => $barang->barang->satuan ?? '-',
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $jumlah * $harga,
                    'vendor' => $barang->barang->vendor->nama ?? '-',
                ]);
            }

            foreach ($data->barangLainnya as $barangLainnya) {

                $items->push([
                    'nama_barang' => $barangLainnya->nama ?? '-',
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
            'total_harga' => $grouped->sum('subtotal'),
            'total_item' => $grouped->count(),
            'items' => $grouped,
        ], 'Data cetak berkas berhasil diambil');
    }
}
