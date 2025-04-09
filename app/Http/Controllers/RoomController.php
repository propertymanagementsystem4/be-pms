<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Services\CodeGeneratorService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    use ApiResponse;

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

            $propertyCode = $type->property->property_code;

            $roomCode = CodeGeneratorService::generateRoomCode($propertyCode);
            
            if (Room::where('room_code', $roomCode)->exists()) {
                return $this->errorResponse('Room code already exists', 422);
            }

            $room = Room::create([
                'id_room' => Str::uuid(),
                'type_id' => $request->type_id,
                'name' => $request->name,
                'description' => $request->description,
                'room_code' => $roomCode,
            ]);
    
            return $this->successResponse(200, $room, 'Room created successfully');
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

            $room = Room::find($id);
            if (!$room) {
                return $this->notFoundResponse('Room not found');
            }

            $room->update($request->validated());
            return $this->successResponse(200, $room, 'Room updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update room: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update room');
        }
    }

    public function destroyRoom($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid room ID format');
            }

            $room = Room::find($id);
            if (!$room) {
                return $this->notFoundResponse('Room not found');
            }

            $room->delete();
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

