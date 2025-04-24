<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\CalculateTotalPriceRequest;
use App\Http\Requests\Reservation\CreateReservationByWhatsappRequest;
use App\Http\Requests\Reservation\CreateReservationRequest;
use App\Http\Requests\Reservation\GetAvailablePropertiesRequest;
use App\Http\Requests\Reservation\GetDetailReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Models\CustomerData;
use App\Models\Facility;
use App\Models\FacilityReservationDetail;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomReservationDetail;
use App\Models\User;
use App\Services\CodeGeneratorService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    use ApiResponse;

    public function getAvailablePropertiesAndRooms(GetAvailablePropertiesRequest $request)
    {
        $checkInDate = Carbon::parse($request->checkIn);
        $checkOutDate = Carbon::parse($request->checkOut);
        $currentDate = Carbon::now()->toDateString();

        if ($checkInDate < $currentDate || $checkOutDate < $currentDate) {
            return $this->badRequestResponse(400, 'Check in and check out date must be greater than or equal to the current date');
        }

        $pageSize = $request->input('size', 10);
        $pageNumber = $request->input('page', 1);
        $skip = ($pageNumber - 1) * $pageSize;

        try {
            // Get all booked room IDs within the selected date range
            $bookedRoomIds = RoomReservationDetail::whereHas('reservation', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->where('check_in_date', '<=', $checkOutDate)
                    ->where('check_out_date', '>=', $checkInDate);
                })->whereIn('reservation_status', ['PENDING', 'CONFIRMED', 'COMPLETED']);
            })->pluck('room_id')->toArray();

            // Get properties with rooms that are NOT booked
            $properties = Property::with(['images', 'types.rooms.images', 'facilities'])
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->when($request->province, function ($query, $province) {
                    $query->where('province', $province);
                })
                ->when($request->city, function ($query, $city) {
                    $query->where('city', $city);
                })
                ->whereHas('types.rooms', function ($query) use ($bookedRoomIds) {
                    $query->whereNotIn('id_room', $bookedRoomIds);
                })
                ->skip($skip)
                ->take($pageSize)
                ->orderBy('name')
                ->get();

            $totalProperties = Property::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->province, function ($query, $province) {
                $query->where('province', $province);
            })
            ->when($request->city, function ($query, $city) {
                $query->where('city', $city);
            })
            ->whereHas('types.rooms', function ($query) use ($bookedRoomIds) {
                $query->whereNotIn('id_room', $bookedRoomIds);
            })
            ->count();

            $totalPages = ceil($totalProperties / $pageSize);

            $response = [
                'property' => $properties->map(function ($property) use ($bookedRoomIds) {
                    return [
                        'property_id' => $property->id_property,
                        'property_code' => $property->property_code,
                        'name' => $property->name,
                        'description' => $property->description,
                        'province' => $property->province,
                        'city' => $property->city,
                        'location' => $property->location,
                        'total_rooms' => $property->total_rooms,
                        'types' => $property->types->map(function ($type) use ($bookedRoomIds) {
                            return [
                                'type_id' => $type->id_type,
                                'name' => $type->name,
                                'price_per_night' => $type->price_per_night,
                                'rooms' => $type->rooms->whereNotIn('id_room', $bookedRoomIds)->values()->map(function ($room) {
                                    return [
                                        'room_id' => $room->id_room,
                                        'room_code' => $room->room_code,
                                        'name' => $room->name,
                                        'description' => $room->description,
                                        'images' => $room->images->map(function ($image) {
                                            return [
                                                'image_id' => $image->id,
                                                'image_url' => asset($image->img_url),
                                            ];
                                        }),
                                    ];
                                }),
                            ];
                        }),
                        'facilities' => $property->facilities->map(function ($facility) {
                            return [
                                'facility_id' => $facility->id,
                                'facility_code' => $facility->facility_code,
                                'name' => $facility->name,
                                'description' => $facility->description,
                                'price' => $facility->price,
                            ];
                        }),
                        'images' => $property->images->map(function ($image) {
                            return [
                                'image_id' => $image->id,
                                'image_url' => asset($image->img_url),
                            ];
                        }),
                    ];
                }),
                'totalPages' => $totalPages,
                'previousPage' => $pageNumber > 1 ? $pageNumber - 1 : null,
                'nextPage' => $pageNumber < $totalPages ? $pageNumber + 1 : null,
                'currentPage' => $pageNumber,
                'pageSize' => $pageSize,
            ];

            return $this->successResponse(200, $response, 'Available properties retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve available properties: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve available properties');
        }
    }

    public function getAllReservations()
    {
        try {
            $reservations = Reservation::withCount(['roomReservationDetails', 'facilityReservationDetails', 'customerData'])
                ->with(['customer', 'admin', 'property'])
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($reservation) {
                    return [
                        'reservation_id' => $reservation->id_reservation,
                        'invoice_number' => $reservation->invoice_number,
                        'property' => [
                            'property_id' => $reservation->property->id_property,
                            'property_code' => $reservation->property->property_code,
                            'name' => $reservation->property->name,
                            'province' => $reservation->property->province,
                            'city' => $reservation->property->city,
                            'location' => $reservation->property->location,
                        ],
                        'admin' => [
                            'user_id' => $reservation->admin->id_user,
                            'fullname' => $reservation->admin->fullname,
                            'email' => $reservation->admin->email,
                        ],
                        'customer' => [
                            'user_id' => $reservation->customer->id_user,
                            'fullname' => $reservation->customer->fullname,
                            'email' => $reservation->customer->email,
                        ],
                        'check_in_date' => Carbon::parse($reservation->check_in_date)->format('Y-m-d'),
                        'check_out_date' => Carbon::parse($reservation->check_out_date)->format('Y-m-d'),
                        'reservation_status' => $reservation->reservation_status,
                        'payment_status' => $reservation->payment_status,
                        'total_price' => $reservation->total_price,
                        'total_guest' => $reservation->total_guest,
                        'total_rooms' => $reservation->room_reservation_details_count,
                        'total_facilities' => $reservation->facility_reservation_details_count,
                        'total_customer_data' => $reservation->customer_data_count,
                    ];
                });

            return $this->successResponse(200, $reservations, 'Reservations retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve reservations: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve reservations');
        }
    }

    public function getDetailReservation(GetDetailReservationRequest $request)
    {
        $reservationId = $request->query('reservation_id');

        try {
            $reservation = Reservation::with([
                'customer',
                'admin',
                'property',
                'roomReservationDetails.room',
                'facilityReservationDetails.facility',
                'customerData',
            ])->where('id_reservation', $reservationId)->firstOrFail();

            if (!$reservation) {
                return $this->notFoundResponse('Reservation not found');
            }

            $response = [
                'reservation_id' => $reservation->id_reservation,
                'invoice_number' => $reservation->invoice_number,
                'property' => [
                    'property_id' => $reservation->property->id_property,
                    'property_code' => $reservation->property->property_code,
                    'name' => $reservation->property->name,
                    'province' => $reservation->property->province,
                    'city' => $reservation->property->city,
                    'location' => $reservation->property->location,
                ],
                'admin' => [
                    'admin_id' => $reservation->admin->id_user,
                    'fullname' => $reservation->admin->fullname,
                    'email' => $reservation->admin->email,
                ],
                'customer' => [
                    'user_id' => $reservation->customer->id_user,
                    'fullname' => $reservation->customer->fullname,
                    'email' => $reservation->customer->email,
                ],
                'check_in_date' => Carbon::parse($reservation->check_in_date)->format('Y-m-d'),
                'check_out_date' => Carbon::parse($reservation->check_out_date)->format('Y-m-d'),
                'reservation_status' => $reservation->reservation_status,
                'payment_status' => $reservation->payment_status,
                'total_price' => $reservation->total_price,
                'total_guest' => $reservation->total_guest,
                'rooms' => $reservation->roomReservationDetails->map(function ($roomDetail) {
                    return [
                        'room_id' => $roomDetail->room->id_room,
                        'room_code' => $roomDetail->room->room_code,
                        'room_name' => $roomDetail->room->name,
                        'room_type' => $roomDetail->room->type,
                        'quantity' => $roomDetail->quantity,
                        'total_room_price' => $roomDetail->price,
                    ];
                }),
                'facilities' => $reservation->facilityReservationDetails->map(function ($facilityDetail) {
                    return [
                        'facility_id' => $facilityDetail->facility->id_facility,
                        'facility_code' => $facilityDetail->facility->facility_code,
                        'facility_name' => $facilityDetail->facility->name,
                        'facility_type' => $facilityDetail->facility->type,
                        'quantity' => $facilityDetail->quantity,
                        'total_facility_price' => $facilityDetail->price,
                    ];
                }),
                'customer_data' => $reservation->customerData ? [
                    'customer_data_id' => $reservation->customerData->id_customer_data,
                    'fullname' => $reservation->customerData->fullname,
                    'nik' => $reservation->customerData->nik,
                    'birth_date' => $reservation->customerData->birth_date
                        ? Carbon::parse($reservation->customerData->birth_date)->format('Y-m-d')
                        : null,
                ] : null,
            ];

            return $this->successResponse(200, $response, 'Detail reservation retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get detail reservation: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to get detail reservation');
        }
    }

    public function storeReservation(CreateReservationRequest $request)
    {
        $checkInDate = Carbon::parse($request->input('checkInDate'))->setTimeFrom(Carbon::now());
        $checkOutDate = Carbon::parse($request->input('checkOutDate'))->setTimeFrom(Carbon::now());
        $currentDate = Carbon::now()->toDateString();

        if ($checkInDate < $currentDate || $checkOutDate < $currentDate) {
            return $this->badRequestResponse(400, 'Check in and check out date must be greater than or equal to the current date');
        }

        $propertyCode = $request->input('property_code');
        $checkInDate = Carbon::parse($request->input('checkInDate'))->setTimeFrom(Carbon::now());
        $checkOutDate = Carbon::parse($request->input('checkOutDate'))->setTimeFrom(Carbon::now());
        $totalGuest = $request->input('totalGuest');
        $roomsData = $request->input('rooms');
        $facilitiesData = $request->input('facilities', []);
        $customerData = $request->input('customerData');
        $customerEmail = $request->input('customerEmail') ?? $customerData[0]['email'] ?? null;
        $currentUser = $request->user(); // Assuming you have authentication middleware
        $invoiceNumber = CodeGeneratorService::generateInvoiceNumber($propertyCode);

        DB::beginTransaction();

        try {
            $findCustomer = null;
            if ($customerEmail) {
                $findCustomer = User::where('email', $customerEmail)->first();
            }

            $property = Property::where('property_code', $propertyCode)->firstOrFail();

            $roomDetails = [];
            $totalRoomPrice = 0;
            $nights = $checkInDate->copy()->startOfDay()->diffInDays($checkOutDate->copy()->startOfDay());
            foreach ($roomsData as $roomInput) {
                $room = Room::with('type')->where('room_code', $roomInput['room_code'])->firstOrFail();
                $pricePerNight = $room->type->price_per_night ?? 0;
                $totalPriceForThisRoom = $pricePerNight * $nights;
                $roomDetails[] = [
                    'room_id' => $room->id_room,
                    'price' => $totalPriceForThisRoom,
                    'quantity' => 1, // Assuming quantity is always 1 in the request
                ];
                $totalRoomPrice += $totalPriceForThisRoom;

                Log::info('Price per night: ' . $pricePerNight);
                Log::info('Nights: ' . $nights);
                Log::info('Total price for this room: ' . $totalPriceForThisRoom);
                Log::info('Total room price: ' . $totalRoomPrice);
            }

            $facilityDetails = [];
            $totalFacilityPrice = 0;
            if ($facilitiesData) {
                foreach ($facilitiesData as $facilityInput) {
                    $facility = Facility::where('facility_code', $facilityInput['facility_code'])->firstOrFail();
                    $facilityDetails[] = [
                        'facility_id' => $facility->id_facility,
                        'price' => $facility->price,
                        'quantity' => 1, // Assuming quantity is always 1 in the request
                    ];
                    $totalFacilityPrice += $facility->price;
                }
            }

            $reservation = new Reservation();
            $reservation->id_reservation = Str::uuid();
            $reservation->invoice_number = $invoiceNumber;
            $reservation->check_in_date = $checkInDate;
            $reservation->check_out_date = $checkOutDate;
            $reservation->total_guest = $totalGuest;
            $reservation->reservation_status = 'PENDING'; // Assuming these constants will be defined or used as strings
            $reservation->payment_status = 'PENDING';
            $reservation->total_price = $totalRoomPrice + $totalFacilityPrice;
            $reservation->customer_id = $findCustomer ? $findCustomer->id_user : null;
            $reservation->admin_id = $currentUser ? $currentUser->id_user : null; // Assign admin if authenticated
            $reservation->property_id = $property->id_property;
            $reservation->save();
            Log::info('Total price booked: ' . $reservation->total_price);

            foreach ($roomDetails as $detail) {
                RoomReservationDetail::create([
                    'id_room_reservation_detail' => Str::uuid(),
                    'reservation_id' => $reservation->id_reservation,
                    'room_id' => $detail['room_id'],
                    'price' => $detail['price'],
                    'quantity' => $detail['quantity'],
                ]);
            }

            foreach ($facilityDetails as $detail) {
                FacilityReservationDetail::create([
                    'id_facility_reservation_detail' => Str::uuid(),
                    'reservation_id' => $reservation->id_reservation,
                    'facility_id' => $detail['facility_id'],
                    'price' => $detail['price'],
                    'quantity' => $detail['quantity'],
                ]);
            }

            foreach ($customerData as $data) {
                CustomerData::create([
                    'id_customer_data' => Str::uuid(),
                    'reservation_id' => $reservation->id_reservation,
                    'fullname' => $data['fullname'],
                    'email' => $data['email'] ?? $customerEmail ?? null,
                    'nik' => $data['nik'] ?? null,
                    'birth_date' => isset($data['birth_date']) ? Carbon::parse($data['birth_date']) : null,
                ]);
            }

            DB::commit();
        
            return $this->successResponse(200, null, 'Create reservation successfully');
        }  catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create reservation: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create reservation');
        }
    }

    public function updateReservation(UpdateReservationRequest $request)
    {
        $reservationId = $request->input('reservation_id');
        $action = $request->input('action');

        try {
            $reservation = Reservation::findOrFail($reservationId);
            if (!$reservation) {
                return $this->notFoundResponse('Reservation not found');
            }

            if ($reservation->reservation_status === 'CANCELLED') {
                return response()->json([
                    'message' => 'Cannot update a cancelled reservation.'
                ], 400);
            }

            if ($reservation->reservation_status === 'COMPLETED') {
                return response()->json([
                    'message' => 'Cannot update a completed reservation.'
                ], 400);
            }

            switch ($action) {
                case 'CONFIRM':
                    $reservation->update([
                        'payment_status' => 'PAID',
                        'reservation_status' => 'CONFIRMED',
                    ]);
                    $message = "Reservation confirmed successfully for reservation ID: {$reservationId}";
                    break;
                case 'CANCEL':
                    $reservation->update([
                        'payment_status' => 'REFUNDED',
                        'reservation_status' => 'CANCELLED',
                    ]);
                    $message = "Reservation cancelled successfully for reservation ID: {$reservationId}";
                    break;
                case 'COMPLETE':
                    if ($reservation->payment_status === 'PENDING') {
                        return response()->json([
                            'message' => 'Cannot complete reservation. Payment is still pending.'
                        ], 400);
                    }

                    $reservation->update([
                        'reservation_status' => 'COMPLETED',
                    ]);
                    $message = "Reservation completed successfully for reservation ID: {$reservationId}";
                    break;
                default:
                    return response()->json(['message' => 'Invalid Action'], 400);
            }

            return $this->successResponse(200, null, $message);
        } catch (\Exception $e) {
            Log::error('Failed to update reservation: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update reservation');
        }
    }

    public function storeReservationDirectWhatsApp(CreateReservationByWhatsappRequest $request)
    {
        $propertyCode = $request->input('property_code');
        $checkInDate = Carbon::parse($request->input('checkInDate'))->setTimeFrom(Carbon::now());
        $checkOutDate = Carbon::parse($request->input('checkOutDate'))->setTimeFrom(Carbon::now());
        $totalGuest = $request->input('totalGuest');
        $roomsData = $request->input('rooms');
        $facilitiesData = $request->input('facilities', []);
        $customerData = $request->input('customerData');
        $currentUser = $request->user(); // Assuming you have authentication middleware

        try {
            $property = Property::where('property_code', $propertyCode)
                ->with('propertyManagers.user')
                ->firstOrFail();

            if (!$property) {
                return $this->notFoundResponse('Property not found');
            }

            $roomsDetails = Room::whereIn('room_code', collect($roomsData)->pluck('room_code'))->get(['name', 'room_code']);
            $facilitiesDetails = Facility::whereIn('facility_code', collect($facilitiesData)->pluck('facility_code'))->get(['name', 'facility_code']);

            $message = $this->generateWhatsAppMessage([
                'property' => $property,
                'checkInDate' => $checkInDate,
                'checkOutDate' => $checkOutDate,
                'totalGuest' => $totalGuest,
                'roomsData' => $roomsDetails,
                'facilitiesData' => $facilitiesDetails,
                'customerData' => $customerData,
                'currentUser' => $currentUser,
            ]);

            $encodedMessage = urlencode($message);
            $adminWhatsAppNumbers = $property->propertyManagers
                ->map(function ($manager) {
                    return optional($manager->user)->phone_number;
                })
                ->filter()
                ->values();

            if ($adminWhatsAppNumbers->isEmpty()) {
                return $this->badRequestResponse(400, 'No admin with phone number found for this property.');
            }

            $whatsappNumber = $adminWhatsAppNumbers->isNotEmpty() ? $adminWhatsAppNumbers->random() : env('WHATSAPP_NUMBER');
            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";

            return $this->successResponse(200, ['whatsappLink' => $whatsappLink], 'WhatsApp link generated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create reservation direct whatsapp: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create reservation direct whatsapp');
        }
    }

    private function generateWhatsAppMessage(array $data): string
    {
        $message = "*New Reservation Inquiry*\n\n";
        $message .= "*Property:* {$data['property']->name} ({$data['property']->property_code})\n";
        $message .= "*Check-in Date:* {$data['checkInDate']}\n";
        $message .= "*Check-out Date:* {$data['checkOutDate']}\n";
        $message .= "*Total Guests:* {$data['totalGuest']}\n\n";

        if ($data['roomsData']->isNotEmpty()) {
            $message .= "*Rooms:*\n";
            foreach ($data['roomsData'] as $room) {
                $message .= "- {$room->name} ({$room->room_code})\n";
            }
            $message .= "\n";
        }

        if ($data['facilitiesData']->isNotEmpty()) {
            $message .= "*Facilities:*\n";
            foreach ($data['facilitiesData'] as $facility) {
                $message .= "- {$facility->name} ({$facility->facility_code})\n";
            }
            $message .= "\n";
        }

        if (!empty($data['customerData'])) {
            $message .= "*Customer Details:*\n";
            foreach ($data['customerData'] as $customer) {
                $message .= "- Name: {$customer['fullname']}";
                if (isset($customer['nik'])) {
                    $message .= ", NIK: {$customer['nik']}";
                }
                if (isset($customer['birth_date'])) {
                    $message .= ", Birth Date: {$customer['birth_date']}";
                }
                $message .= "\n";
            }
            $message .= "\n";
        }

        if ($data['currentUser']) {
            $message .= "*Requested By:* {$data['currentUser']->fullname} ({$data['currentUser']->email})\n";
        }

        $message .= "\nPlease follow up with this inquiry.";

        return $message;
    }
}
