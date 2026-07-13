<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $data = User::with([
            'jabatan',
            'unit',
        ])->paginate(10);

        return ApiResponse::success($data);
    }

    public function show($id)
    {
        $data = User::with([
            'jabatan',
            'unit',
        ])->findOrFail($id);

        return ApiResponse::success($data);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nip' => 'nullable',
            'jenis_kelamin' => 'required|in:Pria,Wanita',
            'jabatan_id' => 'required|exists:jabatans,id',
            'unit_id' => 'required|exists:unit_types,id',
            'role' => 'required|in:admin,user',
        ]);

        $validated['password'] = Hash::make(
            $validated['password']
        );

        $data = User::create($validated);

        return ApiResponse::success(
            $data->load(['jabatan', 'unit']),
            'user berhasil dibuat'
        );
    }

    public function update(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes',
            'email' => "sometimes|email|unique:users,email,$id",
            'nip' => 'sometimes',
            'jenis_kelamin' => 'sometimes|in:Pria,Wanita',
            'jabatan_id' => 'sometimes|exists:jabatans,id',
            'unit_id' => 'sometimes|exists:unit_types,id',
            'role' => 'sometimes|in:admin,user',
            'password' => 'sometimes|min:6',
        ]);

        if (isset($validated['password'])) {

            $validated['password'] = bcrypt(
                $validated['password']
            );
        }

        $user->update($validated);

        return ApiResponse::success(
            $user->load(['jabatan', 'unit']),
            'user berhasil diupdate'
        );
    }

    public function destroy($id)
    {

        User::destroy($id);

        return ApiResponse::success(
            null,
            'user berhasil dihapus'
        );
    }

    public function resetPassword(Request $request, $id)
    {

        $request->validate([
            'password' => 'required|min:6',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'password' => Hash::make(
                $request->password
            ),
        ]);

        return ApiResponse::success(null, 'password berhasil direset');
    }

    public function updateProfile(Request $request)
    {

        $user = auth()->user();

        $validated = $request->validate([
            'username' => 'sometimes',
            'foto' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {

            if ($user->foto) {

                Storage::disk('public')->delete(
                    $user->foto
                );
            }

            $path = $request->file('foto')
                ->store('profile', 'public');

            $user->foto = $path;
        }

        if (isset($validated['username'])) {

            $user->username = $validated['username'];
        }

        $user->save();

        return ApiResponse::success(
            $user,
            'profile berhasil diupdate'
        );
    }

    public function syncFromApi()
    {
        set_time_limit(0);

        $response = Http::get('https://api.unikom.ac.id/v1/structural');

        if (! $response->successful()) {
            return ApiResponse::error('Gagal ambil data API');
        }

        $result = $response->json();
        $groups = $result['data'] ?? [];

        $flatten = [];

        foreach ($groups as $group) {
            foreach ($group as $item) {
                $flatten[] = $item;
            }
        }

        $hashedPassword = bcrypt('default123');

        $jabatanCache = [];
        $unitCache = [];

        $usersToInsert = [];

        foreach ($flatten as $item) {

            $nama = $item['nama'] ?? null;
            $nip = $item['nip'] ?? null;

            if (! $nama || ! $nip) {
                continue;
            }

            if (empty($item['nama_jabatan'])) {
                continue;
            }

            $username = trim(
                ($item['gelar_depan'] ?? '').' '.
                $nama.' '.
                ($item['gelar_belakang'] ?? '')
            );

            $email = ($nip.'_'.md5($item['nama_jabatan'])).'@unikom.ac.id';

            $fullJabatan = strtolower($item['nama_jabatan']);

            if (str_contains($fullJabatan, 'wakil ketua')) {
                $jabatanName = 'Wakil Ketua';

            } elseif (str_contains($fullJabatan, 'ketua')) {
                $jabatanName = 'Ketua';

            } elseif (str_contains($fullJabatan, 'deputi')) {
                $jabatanName = 'Deputi Wakil Rektor';

            } elseif (str_contains($fullJabatan, 'wakil direktur')) {
                $jabatanName = 'Wakil Direktur';

            } elseif (
                str_contains($fullJabatan, 'direktur') ||
                str_contains($fullJabatan, 'direktorat')
            ) {
                $jabatanName = 'Direktur';

            } elseif (str_contains($fullJabatan, 'wakil rektor')) {
                $jabatanName = 'Wakil Rektor';

            } elseif (str_contains($fullJabatan, 'rektor')) {
                $jabatanName = 'Rektor';

            } elseif (str_contains($fullJabatan, 'dekan')) {
                $jabatanName = 'Dekan';

            } elseif (str_contains($fullJabatan, 'kaprodi')) {
                $jabatanName = 'Kaprodi';

            } elseif (str_contains($fullJabatan, 'sekretaris')) {
                $jabatanName = 'Sekretaris';

            } elseif (str_contains($fullJabatan, 'upt')) {
                $jabatanName = 'Kepala UPT';

            } else {
                continue; 
            }

            if (! isset($jabatanCache[$jabatanName])) {
                $jabatanCache[$jabatanName] = Jabatan::firstOrCreate([
                    'nama' => $jabatanName,
                ])->id;
            }

            $unitName = $item['fakultas']
                ?? $item['unit']
                ?? $item['program_studi']
                ?? null;

            if (! $unitName) {
                continue;
            }

            if (! isset($unitCache[$unitName])) {
                $unitCache[$unitName] = UnitType::firstOrCreate([
                    'nama' => $unitName,
                ])->id;
            }

            $usersToInsert[] = [
                'username' => substr($username, 0, 255),
                'nip' => $nip,
                'email' => $email,
                'jenis_kelamin' => 'Pria',
                'jabatan_id' => $jabatanCache[$jabatanName],
                'unit_id' => $unitCache[$unitName],
                'role' => 'user',
                'password' => $hashedPassword,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($usersToInsert, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        return ApiResponse::success([
            'total' => count($usersToInsert),
        ], 'Sync berhasil');
    }

    public function dropdown()
    {
        $data = User::with(['jabatan', 'unit'])->get();

        $grouped = [];

        foreach ($data as $user) {
            if (! $user->jabatan || ! $user->unit) {
                continue;
            }

            $jabatan = $user->jabatan->nama;
            $unit = $user->unit->nama;

            if (! isset($grouped[$jabatan])) {
                $grouped[$jabatan] = [];
            }

            if (! in_array($unit, $grouped[$jabatan])) {
                $grouped[$jabatan][] = $unit;
            }
        }

        $result = [];

        foreach ($grouped as $jabatan => $units) {
            $result[] = [
                'nama' => $jabatan,
                'units' => array_values($units),
            ];
        }

        return ApiResponse::success($result);
    }
}
