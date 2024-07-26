<?php

namespace App\Console\Commands;

use App\Jobs\CheckExpiredBookingsJob;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for bookings that have not been paid within the given timeframe and update their status to expired.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CheckExpiredBookingsJob::dispatch();
        $this->info('CheckExpiredBookingsJob has been dispatched.');
    }
}