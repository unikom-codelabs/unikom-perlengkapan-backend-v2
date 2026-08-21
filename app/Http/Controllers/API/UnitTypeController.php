<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitType\StoreUnitTypeRequest;
use App\Http\Requests\UnitType\UpdateUnitTypeRequest;
use App\Http\Resources\UnitTypeResource;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UnitTypeController extends Controller
{
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

    public function store(StoreUnitTypeRequest $request)
    {
        $data = UnitType::create($request->validated());
        Cache::forget('dropdown_units');

        return ApiResponse::created(
            new UnitTypeResource($data)
        );
    }

    public function show(UnitType $unitType)
    {
        $unitType->load('childrenRecursive');

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    public function update(UpdateUnitTypeRequest $request, UnitType $unitType)
    {
        $unitType->update($request->validated());
        Cache::forget('dropdown_units');

        return ApiResponse::success(
            new UnitTypeResource($unitType)
        );
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->delete();
        Cache::forget('dropdown_units');

        return ApiResponse::deleted();
    }
}
