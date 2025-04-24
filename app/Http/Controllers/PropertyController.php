<?php

namespace App\Http\Controllers;

use App\Http\Requests\Property\AdminToFromPropertyRequest;
use App\Http\Requests\Property\DeletePropertyRequest;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\PropertyManager;
use App\Models\Type;
use App\Services\CodeGeneratorService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    use ApiResponse;

    // OWNER
    public function getAllProperty(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $properties = Property::with([
                'types.rooms',
                'images' => function ($query) {
                    $query->select('property_id', 'img_url');
                },
                'propertyManagers' => function ($query) {
                    $query->select('property_id');
                },
            ])
            ->withCount(['facilities'])
            ->orderBy('name')
            ->paginate($perPage);

            $data = $properties->getCollection()->transform(function ($property) {
                $totalRooms = $property->types->flatMap(function ($type) {
                    return $type->rooms;
                })->count();

                return [
                    'id_property' => $property->id_property,
                    'name' => $property->name,
                    'location' => $property->location,
                    'city' => $property->city,
                    'province' => $property->province,
                    'image_url' => $property->images->pluck('img_url')->first(),
                    'total_rooms' => $totalRooms,
                    'total_facilities' => $property->facilities_count,
                    'total_admins' => $property->propertyManagers->count()
                ];
            });

            return $this->successResponse(200, [
                'data' => $data,
                'pagination' => [
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage()
                ]
            ], 'Properties retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve properties: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve properties');
        }
    }

    public function storeProperty(StorePropertyRequest $request)
    {
        try {
            $propertyCode = CodeGeneratorService::generatePropertyCode();

            if (Property::where('property_code', $propertyCode)->exists()) {
                return $this->errorResponse('Property code already exists', 422);
            }

            $property = Property::create([
                'id_property' => Str::uuid(),
                'name' => $request->name,
                'description' => $request->description,
                'location' => $request->location,
                'total_rooms' => 0,
                'property_code' => $propertyCode,
                'city' => $request->city,
                'province' => $request->province,
            ]);

            $typesData = [];
            foreach ($request->types as $type) {
                $typesData[] = [
                    'id_type' => Str::uuid(),
                    'property_id' => $property->id_property,
                    'name' => $type['name'],
                    'price_per_night' => $type['price_per_night'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Type::insert($typesData);
    
            return $this->successResponse(201, $property, 'Property created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create property');
        }
    }

    public function getDetailProperty($propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $property = Property::with([
                'types.rooms',
                'facilities' => function ($query) {
                    $query->select('id_facility', 'property_id', 'name', 'price');
                },
                'propertyManagers.user',
                'images' => function ($query) {
                    $query->select('property_id', 'img_url');
                }
            ])->find($propertyId);

            if (!$property) {
                return $this->notFoundResponse('Property not found');
            }

            return $this->successResponse(200, [
                'id_property' => $property->id_property,
                'name' => $property->name,
                'location' => $property->location,
                'city' => $property->city,
                'province' => $property->province,
                'image_urls' => $property->images->pluck('img_url'),
                'total_rooms' => $property->types->flatMap(function ($type) {
                    return $type->rooms;
                })->count(),
                'types' => $property->types->map(function ($type) {
                    return [
                        'name' => $type->name,
                        'price_per_night' => $type->price_per_night,
                        'rooms' => $type->rooms->map(function ($room) {
                            return [
                                'name' => $room->name,
                                'description' => $room->description,
                                'price_per_night' => $room->price_per_night
                            ];
                        })
                    ];
                }),
                'total_facilities' => $property->facilities->count(),
                'facilities' => $property->facilities->map(function ($facility) {
                    return [
                        'name' => $facility->name,
                        'price' => $facility->price
                    ];
                }),
                'admins' => $property->propertyManagers->map(function ($propertyManager) {
                    return [
                        'name' => $propertyManager->user->fullname,
                    ];
                }),
            ], 'Property detail retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get property detail: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to get property detail');
        }
    }

    public function updateProperty(UpdatePropertyRequest $request, $propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $property = Property::find($propertyId);
            if (!$property) {
                return $this->notFoundResponse('Property not found');
            }

            $property->update($request->validated());
            return $this->successResponse(200, $property, 'Property updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update property');
        }
    }

    public function destroyProperty(DeletePropertyRequest $request)
    {
        try {
            $ids = $request->validated()['property_ids'];

            foreach ($ids as $id) {
                if (!Str::isUuid($id)) {
                    return $this->badRequestResponse(400, 'Invalid property ID format');
                }
            }

            $existingProperties = Property::whereIn('id_property', $ids)->pluck('id_property')->toArray();

            $notFoundIds = array_diff($ids, $existingProperties);
            if (count($notFoundIds)) {
                return $this->notFoundResponse('Property IDs not found: ' . implode(', ', $notFoundIds));
            }
            
            Property::whereIn('id_property', $ids)->delete();

            return $this->successResponse(200, null, 'Properties deleted successfully');
        } catch (\Exception $e) {
            Log::error('Invalid property ID format: ' . $e->getMessage());
            return $this->internalErrorResponse('Invalid property ID format');
        }
    }

    public function searchProperty(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $query = Property::query();

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $properties = $query->paginate($perPage);

            if ($properties->isEmpty()) {
                return $this->notFoundResponse('No properties found matching your search');
            }

            return $this->successResponse(200, [
                'data' => $properties->items(),
                'pagination' => [
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage()
                ]
            ], 'Properties retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to search properties: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to search properties');
        }
    }

    public function assignAdminToProperty(AdminToFromPropertyRequest $request)
    {
        try {
            $assignedAdmins = [];
            $skippedAdmins = [];
    
            foreach ($request->user_ids as $userId) {
                $exists = PropertyManager::where('user_id', $userId)
                    ->where('property_id', $request->property_id)
                    ->exists();
    
                if ($exists) {
                    $skippedAdmins[] = $userId;
                    continue;
                }
    
                $assigned = PropertyManager::create([
                    'id_property_manager' => Str::uuid(),
                    'user_id' => $userId,
                    'property_id' => $request->property_id,
                ]);
    
                $assignedAdmins[] = $assigned;
            }
    
            return $this->successResponse(201, [
                'assigned' => $assignedAdmins,
                'skipped' => $skippedAdmins,
            ], 'Admins assigned to property successfully');
        } catch (\Exception $e) {
            Log::error('Failed to assign admins to property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to assign admins to property');
        }
    }

    public function deleteAdminFromProperty(AdminToFromPropertyRequest $request)
    {
        try {
            $skippedAdmins = [];
    
            foreach ($request->user_ids as $userId) {
                $manager = PropertyManager::where('user_id', $userId)
                    ->where('property_id', $request->property_id)
                    ->first();
    
                if (!$manager) {
                    $skippedAdmins[] = $userId;
                    continue;
                }
    
                $manager->delete();
            }
    
            return $this->successResponse(200, [
                'skipped' => $skippedAdmins,
            ], 'Admins removed from property successfully');
        } catch (\Exception $e) {
            Log::error('Failed to remove admin(s) from property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to remove admin(s) from property');
        }
    }

    // ADMIN
    public function getAllPropertyManageByAdmin(Request $request, $adminId)
    {
        try {
            if (!Str::isUuid($adminId)) {
                return $this->badRequestResponse(400, 'Invalid admin ID format');
            }

            $perPage = $request->get('per_page', 10);

            $properties = Property::whereIn('id_property', function ($query) use ($adminId) {
                $query->select('property_id')->from('PropertyManager')->where('user_id', $adminId);
            })->with([
                'types.rooms',
                'images' => function ($query) {
                    $query->select('property_id', 'img_url');
                }
            ])
            ->withCount(['facilities'])
            ->paginate($perPage);

            $data = $properties->getCollection()->transform(function ($property) {
                $totalRooms = $property->types->flatMap(function ($type) {
                    return $type->rooms;
                })->count();

                return [
                    'id_property' => $property->id_property,
                    'property_code' => $property->property_code,
                    'name' => $property->name,
                    'location' => $property->location,
                    'image_url' => $property->images->pluck('img_url')->first(),
                    'total_rooms' => $totalRooms,
                    'total_facilities' => $property->facilities_count
                ];
            });

            return $this->successResponse(200, [
                'data' => $data,
                'pagination' => [
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage()
                ]
            ], 'Properties managed by admin retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get properties managed by admin: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to get properties managed by admin');
        }
    }

    public function getDetailPropertyManageByAdmin($adminId, $propertyId)
    {
        try {
            if (!Str::isUuid($adminId) || !Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid ID format');
            }

            $property = Property::where('id_property', $propertyId)
                ->whereIn('id_property', function ($query) use ($adminId) {
                    $query->select('property_id')->from('PropertyManager')->where('user_id', $adminId);
                })
                ->with([
                    'types.rooms',
                    'facilities' => function ($query) {
                        $query->select('id_facility', 'property_id', 'name', 'price');
                    },
                    'images' => function ($query) {
                        $query->select('property_id', 'img_url');
                    }
                ])
                ->first();

            if (!$property) {
                return $this->notFoundResponse('Property not found or not managed by this admin');
            }

            return $this->successResponse(200, [
                'id_property' => $property->id_property,
                'name' => $property->name,
                'location' => $property->location,
                'image_urls' => $property->images->pluck('img_url'),
                'total_rooms' => $property->types->flatMap(function ($type) {
                    return $type->rooms;
                })->count(),
                'types' => $property->types->map(function ($type) {
                    return [
                        'name' => $type->name,
                        'price_per_night' => $type->price_per_night,
                        'rooms' => $type->rooms->map(function ($room) {
                            return [
                                'name' => $room->name,
                                'description' => $room->description,
                                'price_per_night' => $room->price_per_night
                            ];
                        })
                    ];
                }),
                'total_facilities' => $property->facilities->count(),
                'facilities' => $property->facilities->map(function ($facility) {
                    return [
                        'name' => $facility->name,
                        'price' => $facility->price
                    ];
                })
            ], 'Property detail retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get property detail by admin: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to get property detail');
        }
    }

    public function getDetailPropertyByCodeManagedByAdmin($adminId, $propertyCode)
    {
        try {
            if (!Str::isUuid($adminId)) {
                return $this->badRequestResponse(400, 'Invalid admin ID format');
            }

            $property = Property::where('property_code', $propertyCode)
                ->with([
                    'types.rooms',
                    'facilities' => function ($query) {
                        $query->select('id_facility', 'property_id', 'name', 'price');
                    },
                    'images' => function ($query) {
                        $query->select('property_id', 'img_url');
                    }
                ])
                ->first();

            if (!$property) {
                return $this->notFoundResponse('Property with given code not found');
            }

            // Check if admin is assigned to this property
            $isAssigned = DB::table('PropertyManager')
                ->where('user_id', $adminId)
                ->where('property_id', $property->id_property)
                ->exists();

            if (!$isAssigned) {
                return $this->badRequestResponse(403, 'This admin is not assigned to manage this property');
            }

            return $this->successResponse(200, [
                'id_property' => $property->id_property,
                'name' => $property->name,
                'location' => $property->location,
                'image_urls' => $property->images->pluck('img_url'),
                'total_rooms' => $property->types->flatMap(function ($type) {
                    return $type->rooms;
                })->count(),
                'types' => $property->types->map(function ($type) {
                    return [
                        'name' => $type->name,
                        'price_per_night' => $type->price_per_night,
                        'rooms' => $type->rooms->map(function ($room) {
                            return [
                                'name' => $room->name,
                                'description' => $room->description,
                                'price_per_night' => $room->price_per_night
                            ];
                        })
                    ];
                }),
                'total_facilities' => $property->facilities->count(),
                'facilities' => $property->facilities->map(function ($facility) {
                    return [
                        'name' => $facility->name,
                        'price' => $facility->price
                    ];
                })
            ], 'Property detail retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get property detail by code for admin: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to get property detail');
        }
    }

    public function updatePropertyManageByAdmin(UpdatePropertyRequest $request, $adminId, $propertyId)
    {
        try {
            if (!Str::isUuid($adminId) || !Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid ID format');
            }

            $isManager = PropertyManager::where('user_id', $adminId)
                ->where('property_id', $propertyId)
                ->exists();

            if (!$isManager) {
                return $this->badRequestResponse(403, 'This admin is not assigned to manage the property');
            }

            $property = Property::find($propertyId);
            $property->update($request->validated());

            return $this->successResponse(200, $property, 'Property updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update property');
        }
    }

    public function searchPropertyManagedByAdmin(Request $request, $adminId)
    {
        try {
            if (!Str::isUuid($adminId)) {
                return $this->badRequestResponse(400, 'Invalid admin ID format');
            }

            $isAssigned = DB::table('PropertyManager')->where('user_id', $adminId)->exists();

            if (!$isAssigned) {
                return $this->badRequestResponse(403, 'This admin is not assigned to manage any properties');
            }

            $perPage = $request->get('per_page', 10);

            $query = Property::whereHas('propertyManagers', function ($q) use ($adminId) {
                $q->where('user_id', $adminId);
            });

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $properties = $query->paginate($perPage);

            if ($properties->isEmpty()) {
                return $this->notFoundResponse('No properties found matching your search');
            }

            return $this->successResponse(200, [
                'data' => $properties->items(),
                'pagination' => [
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage()
                ]
            ], 'Properties retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to search properties managed by admin: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to search properties managed by admin');
        }
    }
}
