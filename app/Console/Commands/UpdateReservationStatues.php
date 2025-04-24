<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateReservationStatues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:update-statues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically updates reservation statuses based on check-in and check-out dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now()->toDateString();

        $updatedPending = Reservation::where('check_in_date', '<=', $currentDate)
            ->where('reservation_status', 'PENDING')
            ->update(['reservation_status' => 'CANCELLED']);

        $updatedConfirmed = Reservation::where('check_out_date', '<=', $currentDate)
            ->where('reservation_status', 'CONFIRMED')
            ->update(['reservation_status' => 'COMPLETED']);

        $total = $updatedPending + $updatedConfirmed;

        Log::info("✅ [Cron] Updated $total reservations (Pending: $updatedPending, Confirmed: $updatedConfirmed)");

        $this->info("Updated $total reservations.");
    }
}
