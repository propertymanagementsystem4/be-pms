<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogActivityFilterRequest;
use App\Models\Image;
use App\Models\LogActivity;
use App\Models\Reservation;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogActivityController extends Controller
{
    use ApiResponse;

    public function getAllLogActivity(LogActivityFilterRequest $request)
    {
        $logs = LogActivity::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $logs->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $logs = $logs->latest()->get();

        $formattedLogs = $this->formatLogs($logs);

        return $this->successResponse(200, $formattedLogs, 'All log activities retrieved successfully');
    }

    public function getLogActivityByUser(LogActivityFilterRequest $request)
    {
        $userId = auth()->id();

        $logs = LogActivity::where('user_id', $userId);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $logs->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $logs = $logs->latest()->get();

        $formattedLogs = $this->formatLogs($logs);

        return $this->successResponse(200, $formattedLogs, 'User log activities retrieved successfully');
    }

    protected function formatLogs($logs)
    {
        return $logs->map(function ($log) {
            $user = User::find($log->user_id);
            $performedBy = $user->fullname ?? 'Unknown User';

            $date = $log->date ? Carbon::parse($log->date)->translatedFormat('d F Y H:i') : null;

            $action = Str::lower($log->action);
            $module = $log->module_name;
            $description = '';

            if ($module === 'Reservation') {
                $reservation = Reservation::find($log->system_logable_id);
                $invoiceNumber = $reservation?->invoice_number ?? '(no invoice)';
                $description = "{$performedBy} {$action} Reservation with invoice number {$invoiceNumber} pada {$date}";
            } elseif ($module === 'Image') {
                $image = Image::find($log->system_logable_id);
                $imgUrl = $image?->img_url ?? '(no image url)';

                // Get only filename from imgUrl
                $imgFilename = $imgUrl ? basename($imgUrl) : '(no image url)';

                // Special verb for image (if action == created => uploaded)
                $imageAction = $action === 'created' ? 'uploaded' : $action;

                $description = "User {$performedBy} {$imageAction} Image {$imgFilename} pada {$date}";
            } else {
                $oldData = $this->safeJsonDecode($log->old_data);
                $newData = $this->safeJsonDecode($log->new_data);
                $itemName = $newData['name'] ?? $oldData['name'] ?? '(no name)';

                $description = "User {$performedBy} {$action} {$module} {$itemName} pada {$date}";
            }

            return [
                'id' => $log->id_log_activity,
                'description' => $description,
            ];
        });
    }

    protected function safeJsonDecode($data)
    {
        if (!$data) return [];

        $decoded = json_decode($data, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
