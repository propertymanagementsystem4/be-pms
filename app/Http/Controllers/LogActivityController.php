<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogActivityFilterRequest;
use App\Models\LogActivity;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class LogActivityController extends Controller
{
    use ApiResponse;

    public function getAllLogActivity(LogActivityFilterRequest $request)
    {
        $logs = LogActivity::with('user')
            ->when($request->property_id, fn($query) => $query->where('property_id', $request->property_id))
            ->when($request->start_date, fn($query) => $query->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->whereDate('date', '<=', $request->end_date))
            ->orderBy('date', 'asc')
            ->latest()
            ->get();

        return $this->successResponse(200, $this->formatLogs($logs), 'Log activity fetched successfully');
    }

    public function getLogActivityByUser(LogActivityFilterRequest $request)
    {
        $logs = LogActivity::with('user')
            ->where('user_id', auth()->id())
            ->when($request->property_id, fn($query) => $query->where('property_id', $request->property_id))
            ->when($request->start_date, fn($query) => $query->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->whereDate('date', '<=', $request->end_date))
            ->orderBy('date', 'asc')
            ->latest()
            ->get();

        return $this->successResponse(200, $this->formatLogs($logs), 'User log activity fetched successfully');
    }

    protected function formatLogs($logs)
    {
        return $logs->map(function ($log) {
            $userName = $log->user->fullname ?? 'Unknown User';
            $action = $log->action;
            $module = $log->module_name;
            $date = $log->date ? Carbon::parse($log->date)->translatedFormat('d F Y H:i') : null;
            $newData = $log->new_data;
            $oldData = $log->old_data;

            switch ($module) {
                case 'Reservation':
                    $invoiceNumber = $newData['invoice_number'] ?? 'N/A';
                    $description = "$userName $action $module with invoice number $invoiceNumber at $date";
                    break;
                case 'Image':
                    $imageName = $newData['img_url'] ?? $oldData['img_url'] ?? 'unknown image';
                    $description = "$userName $action $module $imageName at $date";
                    break;
                default:
                    $name = $newData['name'] ?? $oldData['name'] ?? 'unknown';
                    $description = "$userName $action $module $name at $date";
                    break;
            }

            return [
                'id' => $log->id_log_activity,
                'description' => $description,
            ];
        });
    }
}
