<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DaftarPengajuan\StoreDaftarPengajuanRequest;
use App\Http\Requests\DaftarPengajuan\StoreFullDaftarPengajuanRequest;
use App\Http\Requests\DaftarPengajuan\UpdateDaftarPengajuanRequest;
use App\Http\Resources\DaftarPengajuanResource;
use App\Models\AktivasiPengajuan;
use App\Models\DaftarPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarPengajuanController extends Controller
{
    public function storeFull(StoreFullDaftarPengajuanRequest $request)
    {
        $data = $request->validated();

        $user = auth()->user()->load('jabatan');

        $jabatan = strtolower($user->jabatan->nama ?? '');

        $allowedTipe = ['tahunan'];

        if (
            str_contains($jabatan, 'dekan') ||
            str_contains($jabatan, 'kaprodi')
        ) {
            $allowedTipe = [
                'tahunan',
                'kelas',
                'ujian',
            ];
        }

        if (! in_array($data['tipe'], $allowedTipe)) {

            return ApiResponse::error(
                'Anda tidak memiliki akses untuk tipe pengajuan ini',
                403
            );
        }

        $aktivasi = AktivasiPengajuan::with('pengajuan')
            ->whereHas('pengajuan', function ($q) use ($data) {
                $q->where('tipe', $data['tipe']);
            })
            ->whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now())
            ->latest('id')
            ->first();

        if (! $aktivasi) {

            return ApiResponse::error(
                'Tidak ada periode pengajuan aktif',
                422
            );
        }

        $sudahMengajukan = DaftarPengajuan::where('user_id', auth()->id())
            ->where('id_aktivasi', $aktivasi->id)
            ->exists();

        if ($sudahMengajukan) {

            return ApiResponse::error(
                'Anda sudah melakukan pengajuan pada periode ini',
                422
            );
        }

        DB::beginTransaction();

        try {
            $pengajuan = DaftarPengajuan::create([
                'id_aktivasi' => $aktivasi->id,
                'user_id' => auth()->id(),
                'date' => now(),
                'surat_pengajuan' => $request->hasFile('surat_pengajuan')
                    ? $request->file('surat_pengajuan')
                        ->store('surat_pengajuan', 'public')
                    : '',
            ]);

            foreach ($data['barang'] ?? [] as $item) {

                if ($item['jumlah'] > 0) {

                    $pengajuan->barang()->create([
                        'id_barang' => $item['id_barang'],
                        'jumlah' => $item['jumlah'],
                        'jumlah_disetujui' => 0,
                        'status' => false,
                    ]);
                }
            }

            $vendorPerlengkapan = \App\Models\Vendor::firstOrCreate(
                ['nama' => 'Perlengkapan'],
                ['kontak' => '-']
            );

            foreach ($data['barang_lainnya'] ?? [] as $index => $item) {

                $buktiFotoPath = null;
                // Form-data with files may place the file directly in the array or in request->file()
                if (isset($item['bukti_foto']) && $item['bukti_foto'] instanceof \Illuminate\Http\UploadedFile) {
                    $buktiFotoPath = $item['bukti_foto']->store('bukti_foto_pengajuan', 'public');
                } elseif ($request->hasFile("barang_lainnya.{$index}.bukti_foto")) {
                    $buktiFotoPath = $request->file("barang_lainnya.{$index}.bukti_foto")->store('bukti_foto_pengajuan', 'public');
                } elseif ($request->hasFile("bukti_foto_{$index}")) {
                    $buktiFotoPath = $request->file("bukti_foto_{$index}")->store('bukti_foto_pengajuan', 'public');
                }

                $pengajuan->barangLainnya()->create([
                    'nama' => $item['nama'],
                    'jumlah' => $item['jumlah'],
                    'kategori' => $item['kategori'],
                    'satuan' => $item['satuan'],
                    'alasan' => $item['alasan'] ?? $item['catatan'] ?? $item['note'] ?? null,
                    'bukti_foto' => $buktiFotoPath,
                    'jumlah_disetujui' => 0,
                    'status' => false,
                    'vendor_id' => $vendorPerlengkapan->id,
                ]);
            }

            DB::commit();

            return ApiResponse::success(
                new DaftarPengajuanResource(
                    $pengajuan->load(
                        'barang.barang.vendor',
                        'barangLainnya'
                    )
                ),
                'Pengajuan berhasil dibuat'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function index()
    {
        return ApiResponse::success(

            DaftarPengajuanResource::collection(

                DaftarPengajuan::with([
                    'user',
                    'aktivasi',
                    'barang.barang',
                    'barangLainnya',
                ])->latest()->get()

            )

        );
    }

    public function store(StoreDaftarPengajuanRequest $request)
    {
        $data = $request->validated();

        $aktivasi = AktivasiPengajuan::where('id', $data['aktivasi_pengajuan_id'])
            ->whereDate('aktif_mulai', '<=', now())
            ->whereDate('aktif_selesai', '>=', now())
            ->first();

        if (! $aktivasi) {
            return ApiResponse::error(
                'Periode pengajuan tidak aktif',
                422
            );
        }

        $data['user_id'] = auth()->id();

        $data['id_aktivasi'] = $data['aktivasi_pengajuan_id'];

        unset($data['aktivasi_pengajuan_id']);

        if ($request->hasFile('surat_pengajuan')) {

            $data['surat_pengajuan'] =
                $request->file('surat_pengajuan')
                    ->store('surat_pengajuan', 'public');
        }

        $pengajuan = DaftarPengajuan::create($data);

        $pengajuan->load([
            'user',
            'aktivasi',
            'barang',
            'barangLainnya',
        ]);

        return ApiResponse::success(
            new DaftarPengajuanResource($pengajuan),
            'Pengajuan berhasil dibuat'
        );
    }

    public function show(DaftarPengajuan $daftarPengajuan)
    {

        return ApiResponse::success(

            new DaftarPengajuanResource(

                $daftarPengajuan->load([
                    'user',
                    'aktivasi',
                    'barang.barang',
                    'barangLainnya',
                ])

            )

        );
    }

    public function update(
        UpdateDaftarPengajuanRequest $request,
        DaftarPengajuan $daftarPengajuan
    ) {

        $daftarPengajuan->update(

            $request->validated()

        );

        return ApiResponse::success(

            new DaftarPengajuanResource($daftarPengajuan)

        );
    }

    public function destroy(DaftarPengajuan $daftarPengajuan)
    {

        $daftarPengajuan->delete();

        return ApiResponse::deleted();
    }

    public function adminIndex(Request $request)
    {
        $aktivasi = $request->id_aktivasi;
        $jabatan = $request->jabatan_id;
        $bagian = $request->bagian_id;
        $status = $request->status;
        $tipe = $request->tipe;

        $query = DaftarPengajuan::with([
            'user.jabatan',
            'user.unit',
            'aktivasi.pengajuan',
            'barang.barang.vendor',
            'barangLainnya',
        ]);

        if ($aktivasi) {

            $query->where('id_aktivasi', $aktivasi);
        }

        if ($jabatan) {

            $query->whereHas('user.jabatan', function ($q) use ($jabatan) {

                $q->where('id', $jabatan);

            });
        }

        if ($bagian) {

            $query->whereHas('user.unit', function ($q) use ($bagian) {

                $q->where('id', $bagian);

            });
        }

        if ($tipe) {

            $query->whereHas('aktivasi.pengajuan', function ($q) use ($tipe) {

                $q->where('tipe', $tipe);

            });
        }

        if ($status !== null) {

            $query->where(function ($q) use ($status) {

                $q->whereHas('barang', function ($sub) use ($status) {

                    $sub->where('status', $status);

                })->orWhereHas('barangLainnya', function ($sub) use ($status) {

                    $sub->where('status', $status);

                });

            });
        }

        $data = $query->latest()->get();

        return ApiResponse::success(

            DaftarPengajuanResource::collection($data),

            'List pengajuan admin'

        );
    }
}
