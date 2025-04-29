<?php

namespace App\Http\Controllers;

use App\Http\Requests\Facility\DeleteFacilityRequest;
use App\Http\Requests\Facility\StoreFacilityRequest;
use App\Http\Requests\Facility\UpdateFacilityRequest;
use App\Models\Facility;
use App\Models\Property;
use App\Services\CodeGeneratorService;
use App\Traits\ApiResponse;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    use ApiResponse, LogActivity;

    public function getAllFacility()
    {
        try {
            $facilities = Facility::all();
            return $this->successResponse(200, $facilities, 'Facilities retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve facilities: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve facilities');
        }
    }

    public function getFacilityByPropertyId($propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $facilities = Facility::where('property_id', $propertyId)->get();
            if ($facilities->isEmpty()) {
                return $this->notFoundResponse('No facilities found for this property');
            }

            return $this->successResponse(200, $facilities, 'Facilities retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve facilities by property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve facilities by property');
        }
    }

    public function storeFacility(StoreFacilityRequest $request)
    {
        try {
            $property = Property::find($request->property_id);
            if (!$property) {
                return $this->notFoundResponse('Property not found for this facility');
            }

            $facilityCode = CodeGeneratorService::generateFacilityCode($property->property_code);

            if (Facility::where('facility_code', $facilityCode)->exists()) {
                return $this->errorResponse('Facility code already exists', 422);
            }

            $facility = Facility::create([
                'id_facility' => Str::uuid(),
                'property_id' => $request->property_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'facility_code' => $facilityCode,
            ]);

            $this->logActivity(
                propertyId: $request->property_id,
                module: 'Facility',
                action: 'Created',
                newData: $facility->toArray(),
            );
    
            return $this->successResponse(201, $facility, 'Facility created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create facility: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create facility');
        }
    }

    public function getDetailFacility($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid facility ID format');
            }

            $facility = Facility::find($id);
            if (!$facility) {
                return $this->notFoundResponse('Facility not found');
            }

            return $this->successResponse(200, $facility, 'Facility retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve facility: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve facility');
        }
    }

    public function updateFacility(UpdateFacilityRequest $request, $id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid facility ID format');
            }

            $facility = Facility::find($id);
            if (!$facility) {
                return $this->notFoundResponse('Facility not found');
            }

            $facility->update($request->validated());

            $this->logActivity(
                propertyId: $facility->property_id,
                module: 'Facility',
                action: 'Updated',
                oldData: $facility->getOriginal(),
                newData: $facility->toArray(),
            );

            return $this->successResponse(200, $facility, 'Facility updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update facility: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update facility');
        }
    }

    public function destroyFacility(DeleteFacilityRequest $request)
    {
        try {
            $facilityIds = $request->validated()['facility_ids'];

            foreach ($facilityIds as $id) {
                if (!Str::isUuid($id)) {
                    return $this->badRequestResponse(400, 'Invalid facility ID format');
                }
            }

            $existingFacilities = Facility::whereIn('id_facility', $facilityIds)->pluck('id_facility')->toArray();
            $notFoundIds = array_diff($facilityIds, $existingFacilities);
            if (count($notFoundIds)) {
                return $this->notFoundResponse('Facility IDs not found: ' . implode(', ', $notFoundIds));
            }

            Facility::whereIn('id_facility', $facilityIds)->get()->each(function ($facility) {
                $facility->delete();
                
                $this->logActivity(
                    propertyId: $facility->property_id,
                    module: 'Facility',
                    action: 'Deleted',
                    oldData: $facility->toArray(),
                );
            });
            
            return $this->successResponse(200, null, 'Facility deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete facility: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete facility');
        }
    }

    public function searchFacility(Request $request, $propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $query = Facility::where('property_id', $propertyId);

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $facilities = $query->orderBy('name', 'asc')->get();

            return $this->successResponse(200, $facilities, 'Facilities retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to search facilities: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to search facilities');
        }
    }
}

