<?php

namespace App\Http\Controllers;

use App\Http\Requests\Type\DeleteTypeRequest;
use App\Http\Requests\Type\StoreTypeRequest;
use App\Http\Requests\Type\UpdateTypeRequest;
use App\Models\Type;
use App\Traits\ApiResponse;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    use ApiResponse, LogActivity;

    public function getTypeByPropertyId($propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $types = Type::where('property_id', $propertyId)->orderBy('price_per_night', 'asc')->get();

            if ($types->isEmpty()) {
                return $this->notFoundResponse('No types found for this property');
            }

            return $this->successResponse(200, $types, 'Types retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve types by property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve types by property');
        }
    }

    public function storeType(StoreTypeRequest $request)
    {
        try {
            $propertyId = $request->input('property_id');
            $typesData = $request->input('types');

            $storedTypes = [];

            foreach ($typesData as $typeData) {
                $storedTypes[] = Type::create([
                    'id_type' => Str::uuid(),
                    'property_id' => $propertyId,
                    'name' => $typeData['name'],
                    'price_per_night' => $typeData['price_per_night'],
                ]);
            }

            foreach ($storedTypes as $type){
                $this->logActivity(
                    propertyId: $propertyId,
                    module: 'Type',
                    action: 'Created',
                    newData: $type->toArray(),
                );
            }
    
            return $this->successResponse(201, $storedTypes, 'Type created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create type: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create type');
        }
    }

    public function getDetailType($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid type ID format');
            }

            $type = Type::find($id);
            if (!$type) {
                return $this->notFoundResponse('Type not found');
            }

            return $this->successResponse(200, $type, 'Type retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve type: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve type');
        }
    }

    public function updateType(UpdateTypeRequest $request, $id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid type ID format');
            }

            $type = Type::find($id);
            if (!$type) {
                return $this->notFoundResponse('Type not found');
            }

            $type->update($request->validated());

            $this->logActivity(
                propertyId: $type->property_id,
                module: 'Type',
                action: 'Updated',
                oldData: $type->getOriginal(),
                newData: $type->toArray(),
            );
            
            return $this->successResponse(200, $type, 'Type updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update type: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update type');
        }
    }

    public function destroyType(DeleteTypeRequest $request)
    {
        try {
            $typeIds = $request->validated()['type_ids'];

            foreach ($typeIds as $id) {
                if (!Str::isUuid($id)) {
                    return $this->badRequestResponse(400, 'Invalid type ID format');
                }
            }

            $existingTypes = Type::whereIn('id_type', $typeIds)->pluck('id_type')->toArray();
            $notFoundIds = array_diff($typeIds, $existingTypes);
            if (count($notFoundIds)) {
                return $this->notFoundResponse('Type IDs not found: ' . implode(', ', $notFoundIds));
            }

            Type::whereIn('id_type', $typeIds)->get()->each(function ($type) {
                $type->delete();
                
                $this->logActivity(
                    propertyId: $type->property_id,
                    module: 'Type',
                    action: 'Deleted',
                    oldData: $type->toArray(),
                );
            });

            return $this->successResponse(200, null, 'Types deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete types: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete types');
        }
    }

    public function searchType(Request $request, $propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $query = Type::where('property_id', $propertyId);

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $types = $query->orderBy('price_per_night', 'asc')->get();

            return $this->successResponse(200, $types, 'Types retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to search types: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to search types');
        }
    }
}
