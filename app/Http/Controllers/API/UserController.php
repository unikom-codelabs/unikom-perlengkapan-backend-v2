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
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/users',
        tags: ['User'],
        summary: 'List user',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List user berhasil diambil'
            ),
        ]
    )]
    public function index(Request $request)
    {
        $query = User::with([
            'jabatan',
            'unit',
        ]);

        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where('username', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        $data = $query->paginate(10);

        return ApiResponse::success($data);
    }

    #[OA\Get(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Detail user',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail user berhasil diambil'
            ),
        ]
    )]
    public function show($id)
    {
        $data = User::with([
            'jabatan',
            'unit',
        ])->findOrFail($id);

        return ApiResponse::success($data);
    }

    #[OA\Post(
        path: '/api/users',
        tags: ['User'],
        summary: 'Tambah user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'email', 'password', 'jenis_kelamin', 'jabatan_id', 'unit_id', 'role'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'gilang'),
                    new OA\Property(property: 'email', type: 'string', example: 'gilang@mail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'nip', type: 'string', example: '12345678'),
                    new OA\Property(property: 'jenis_kelamin', type: 'string', example: 'Pria'),
                    new OA\Property(property: 'jabatan_id', type: 'integer', example: 1),
                    new OA\Property(property: 'unit_id', type: 'integer', example: 1),
                    new OA\Property(property: 'role', type: 'string', example: 'user'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User berhasil dibuat'
            ),
        ]
    )]
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

    #[OA\Put(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Update user',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'gilang update'),
                    new OA\Property(property: 'email', type: 'string', example: 'baru@mail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'nip', type: 'string', example: '87654321'),
                    new OA\Property(property: 'jenis_kelamin', type: 'string', example: 'Wanita'),
                    new OA\Property(property: 'jabatan_id', type: 'integer', example: 1),
                    new OA\Property(property: 'unit_id', type: 'integer', example: 1),
                    new OA\Property(property: 'role', type: 'string', example: 'admin'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User berhasil diupdate'
            ),
        ]
    )]
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

    #[OA\Delete(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Hapus user',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'user berhasil dihapus'
            ),
        ]
    )]
    public function destroy($id)
    {

        User::destroy($id);

        return ApiResponse::success(
            null,
            'user berhasil dihapus'
        );
    }

    #[OA\Patch(
        path: '/api/users/{id}/reset-password',
        tags: ['User'],
        summary: 'Reset password user',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password'],
                properties: [
                    new OA\Property(property: 'password', type: 'string', example: 'passwordbaru123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'password berhasil direset'
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/users/update-profile',
        tags: ['User'],
        summary: 'Update profile user login',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'username', type: 'string', example: 'gilang update'),
                        new OA\Property(
                            property: 'foto',
                            type: 'string',
                            format: 'binary'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'profile berhasil diupdate'
            ),
        ]
    )]
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

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('users')->truncate();
        DB::table('unit_types')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $response = Http::get('https://api.unikom.ac.id/v1/structural');

        if (! $response->successful()) {
            return ApiResponse::error('Gagal ambil data API');
        }

        $result = $response->json();

        $groups = $result['data'] ?? [];

        $hashedPassword = bcrypt('default123');

        $unitCache = [];
        $jabatanCache = [];

        $usersToInsert = [];

        foreach ($groups as $groupName => $items) {

            /*
            |--------------------------------------------------------------------------
            | ROOT TREE
            |--------------------------------------------------------------------------
            */
            $rootName = strtoupper($groupName);

            if (! isset($unitCache[$rootName])) {

                $unitCache[$rootName] = UnitType::create([
                    'nama' => $rootName,
                    'parent_id' => null,
                ])->id;
            }

            $rootId = $unitCache[$rootName];

            foreach ($items as $item) {

                $nama = $item['nama'] ?? null;
                $nip = $item['nip'] ?? null;
                $email = $item['email'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | JANGAN SKIP JABATAN NULL
                |--------------------------------------------------------------------------
                */
                $namaJabatan = $item['nama_jabatan'] ?? '-';

                if (! $nama || ! $nip) {
                    continue;
                }

                $username = trim(
                    ($item['gelar_depan'] ?? '').' '.
                    $nama.' '.
                    ($item['gelar_belakang'] ?? '')
                );

                /*
                |--------------------------------------------------------------------------
                | UNIT ASLI API
                |--------------------------------------------------------------------------
                */
                $unitName =
                    $item['unit']
                    ?? $item['program_studi']
                    ?? $item['fakultas']
                    ?? 'LAINNYA';

                /*
                |--------------------------------------------------------------------------
                | LEVEL 2
                |--------------------------------------------------------------------------
                */
                $secondKey = $rootId.'_'.$unitName;

                if (! isset($unitCache[$secondKey])) {

                    $unitCache[$secondKey] = UnitType::create([
                        'nama' => $unitName,
                        'parent_id' => $rootId,
                    ])->id;
                }

                $unitId = $unitCache[$secondKey];

                /*
                |--------------------------------------------------------------------------
                | JABATAN ASLI API
                |--------------------------------------------------------------------------
                */
                if (! isset($jabatanCache[$namaJabatan])) {

                    $jabatanCache[$namaJabatan] = Jabatan::create([
                        'nama' => $namaJabatan,
                    ])->id;
                }

                /*
                |--------------------------------------------------------------------------
                | INSERT SESUAI API ASLI
                |--------------------------------------------------------------------------
                */
                $usersToInsert[] = [
                    'username' => substr($username, 0, 255),
                    'nip' => $nip,

                    /*
                    |--------------------------------------------------------------------------
                    | kalau email null tetap simpan unique dummy
                    |--------------------------------------------------------------------------
                    */
                    'email' => $email
                        ?: strtolower(str_replace(' ', '', $nip.'_'.md5($namaJabatan))).'@null.unikom',

                    'jenis_kelamin' => 'Pria',

                    /*
                    |--------------------------------------------------------------------------
                    | JABATAN ASLI JANGAN DIMAPPING LAGI
                    |--------------------------------------------------------------------------
                    */
                    'jabatan_id' => $jabatanCache[$namaJabatan],

                    /*
                    |--------------------------------------------------------------------------
                    | UNIT SESUAI API
                    |--------------------------------------------------------------------------
                    */
                    'unit_id' => $unitId,

                    'role' => 'user',
                    'password' => $hashedPassword,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT TANPA NIMPA DATA ORANG LAIN
        |--------------------------------------------------------------------------
        */
        foreach (array_chunk($usersToInsert, 500) as $chunk) {

            DB::table('users')->insert($chunk);
        }

        return ApiResponse::success([
            'total' => count($usersToInsert),
        ], 'Sync berhasil');
    }

    public function dropdown()
    {
        $units = UnitType::with('childrenRecursive')
            ->whereNull('parent_id')
            ->get();

        return ApiResponse::success(
            $this->formatTree($units)
        );
    }

    private function formatTree($units)
    {
        $result = [];

        foreach ($units as $unit) {

            $item = [
                'name' => $unit->nama,
                'unit_id' => $unit->id,
            ];

            if ($unit->childrenRecursive->count()) {

                $item['children'] = $this->formatTree(
                    $unit->childrenRecursive
                );
            }

            $result[] = $item;
        }

        return $result;
    }
}
