<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\DeleteRoomRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Services\CodeGeneratorService;
use App\Traits\ApiResponse;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    use ApiResponse, LogActivity;

    public function getRoomByPropertyId($propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $rooms = Room::whereHas('type', function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })->with('type')->orderBy('name', 'asc')->get();

            if ($rooms->isEmpty()) {
                return $this->notFoundResponse('No rooms found for this property');
            }

            return $this->successResponse(200, $rooms, 'Rooms retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve rooms by property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve rooms');
        }
    }

    public function storeRoom(StoreRoomRequest $request)
    {
        try {
            $type = Type::with('property')->findOrFail($request->type_id);
            
            if (!$type->property) {
                return $this->notFoundResponse('Property not found for this type');
            }

            $property = $type->property;
            $propertyCode = $type->property->property_code;
            $createdRooms = [];

            foreach ($request->rooms as $roomData) {
                // Generate unique room code
                $roomCode = CodeGeneratorService::generateRoomCode($propertyCode);
    
                // Ensure code is unique
                while (Room::where('room_code', $roomCode)->exists()) {
                    $roomCode = CodeGeneratorService::generateRoomCode($propertyCode);
                }
    
                $room = Room::create([
                    'id_room' => Str::uuid(),
                    'type_id' => $request->type_id,
                    'name' => $roomData['name'],
                    'description' => $roomData['description'],
                    'room_code' => $roomCode,
                ]);

                $this->logActivity(
                    propertyId: $type->property->id_property,
                    module: 'Room',
                    action: 'Created',
                    newData: $room->toArray(),
                );
    
                $createdRooms[] = $room;
            }

            $property->increment('total_rooms', count($createdRooms));
    
            return $this->successResponse(200, $createdRooms, 'Room created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to store room: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to store room');
        }
    }

    public function getDetailRoom($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid room ID format');
            }

            $room = Room::with('type')->find($id);
            if (!$room) {
                return $this->notFoundResponse('Room not found');
            }

            return $this->successResponse(200, $room, 'Room retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve detail room: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve detail room');
        }
    }

    public function updateRoom(UpdateRoomRequest $request, $id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid room ID format');
            }

            $room = Room::find($id)->with('type')->first();
            if (!$room) {
                return $this->notFoundResponse('Room not found');
            }

            $room->update($request->validated());

            $this->logActivity(
                propertyId: $room->type->property_id,
                module: 'Room',
                action: 'Updated',
                oldData: $room->getOriginal(),
                newData: $room->toArray(),
            );

            return $this->successResponse(200, $room, 'Room updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update room: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update room');
        }
    }

    public function destroyRoom(DeleteRoomRequest $request)
    {
        $roomIds = $request->validated()['room_ids'];

        try {
            foreach ($roomIds as $id) {
                if (!Str::isUuid($id)) {
                    return $this->badRequestResponse(400, 'Invalid room ID format');
                }
            }

            $existingRooms = Room::whereIn('id_room', $roomIds)->pluck('id_room')->toArray();
            $notFoundIds = array_diff($roomIds, $existingRooms);
            if (count($notFoundIds)) {
                return $this->notFoundResponse('Room IDs not found: ' . implode(', ', $notFoundIds));
            }

            Room::whereIn('id_room', $roomIds)->get()->each(function ($room) {
                $room->delete();

                $this->logActivity(
                    propertyId: $room->type->property_id,
                    module: 'Room',
                    action: 'Deleted',
                    oldData: $room->toArray(),
                );
            });

            return $this->successResponse(200, null, 'Room deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete room: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete room');
        }
    }

    public function searchRoom(Request $request, $propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $query = Room::whereHas('type', function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            });

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $rooms = $query->orderBy('name', 'asc')->get();

            return $this->successResponse(200, $rooms, 'Rooms retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to search rooms: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to search rooms');
        }
    }
}

