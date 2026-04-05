<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitType\StoreUnitTypeRequest;
use App\Http\Requests\UnitType\UpdateUnitTypeRequest;
use App\Http\Resources\UnitTypeResource;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function index()
    {
        $data = UnitType::whereNull('parent_id')
            ->with('children.children')
            ->get();

        return ApiResponse::success(
            UnitTypeResource::collection($data)
        );
    }

    public function store(StoreUnitTypeRequest $request)
    {
        $data = UnitType::create($request->validated());

        return ApiResponse::created(
            new UnitTypeResource($data)
        );
    }

    public function show(UnitType $unitType)
    {
        $unitType->load('children.children');

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    public function update(UpdateUnitTypeRequest $request, UnitType $unitType)
    {
        $unitType->update($request->validated());

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->delete();

        return ApiResponse::deleted();
    }
}
