<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;
    
    public function index()
    {
        $date = Carbon::now();

        $totalSuccessBookingThisMonth = Reservation::whereMonth('check_in_date', $date->month)
            ->whereYear('check_in_date', $date->year)
            ->where('reservation_status', 'COMPLETED')
            ->count();

        $totalEarningsThisMonth = Reservation::whereMonth('check_in_date', $date->month)
            ->whereYear('check_in_date', $date->year)
            ->where('reservation_status', 'COMPLETED')
            ->sum('total_price');

        $totalProperties = Property::count();
        $totalRooms = Room::count();
        $totalFacilities = Facility::count();
        
        $data = [
            'total_success_booking_this_month' => $totalSuccessBookingThisMonth,
            'total_earnings_this_month' => $totalEarningsThisMonth,
            'total_properties' => $totalProperties,
            'total_rooms' => $totalRooms,
            'total_facilities' => $totalFacilities,
        ];

        return $this->successResponse(200, $data, 'Dashboard data retrieved successfully.');
    }
}
