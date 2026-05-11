<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitType\StoreUnitTypeRequest;
use App\Http\Requests\UnitType\UpdateUnitTypeRequest;
use App\Http\Resources\UnitTypeResource;
use App\Models\UnitType;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UnitTypeController extends Controller
{
    #[OA\Get(
        path: '/api/unit-types',
        tags: ['Unit Type'],
        summary: 'List unit type (support filter parent_id)',
        description: 'Jika parent_id tidak dikirim → tampilkan tree parent + children. Jika parent_id=null → hanya parent. Jika parent_id=id → children berdasarkan parent.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'parent_id',
                in: 'query',
                required: false,
                description: 'Filter parent_id (isi null atau id)',
                schema: new OA\Schema(type: 'string', example: 'null')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List unit type berhasil diambil'
            ),
        ]
    )]
    public function index(Request $request)
    {
        $query = UnitType::query();

        if ($request->has('parent_id')) {

            if ($request->parent_id === 'null') {

                $query->whereNull('parent_id');
            } else {

                $query->where('parent_id', $request->parent_id);
            }

            $data = $query->get();
        } else {

            $data = $query
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->get();
        }

        return ApiResponse::success(
            UnitTypeResource::collection($data)
        );
    }

    #[OA\Post(
        path: '/api/unit-types',
        tags: ['Unit Type'],
        summary: 'Tambah unit type',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Fakultas'),
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Unit type berhasil dibuat'
            ),
        ]
    )]
    public function store(StoreUnitTypeRequest $request)
    {
        $data = UnitType::create($request->validated());

        return ApiResponse::created(
            new UnitTypeResource($data)
        );
    }

    #[OA\Get(
        path: '/api/unit-types/{id}',
        tags: ['Unit Type'],
        summary: 'Detail unit type beserta children',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID unit type',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail unit type berhasil diambil'
            ),
        ]
    )]
    public function show(UnitType $unitType)
    {
        $unitType->load('childrenRecursive');

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    #[OA\Put(
        path: '/api/unit-types/{id}',
        tags: ['Unit Type'],
        summary: 'Update unit type',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID unit type',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Program Studi'),
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unit type berhasil diupdate'
            ),
        ]
    )]
    public function update(UpdateUnitTypeRequest $request, UnitType $unitType)
    {
        $unitType->update($request->validated());

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    #[OA\Delete(
        path: '/api/unit-types/{id}',
        tags: ['Unit Type'],
        summary: 'Hapus unit type',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID unit type',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unit type berhasil dihapus'
            ),
        ]
    )]
    public function destroy(UnitType $unitType)
    {
        $unitType->delete();

        return ApiResponse::deleted();
    }
}
