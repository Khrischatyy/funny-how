<?php

namespace App\Jobs;

use App\Mail\BookingConfirmedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;

class BookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $userEmail;
    protected $amount;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Booking $booking
     * @param string $userEmail
     * @param float|null $amount
     */
    public function __construct(Booking $booking, $userEmail, $amount)
    {
        $this->booking = $booking;
        $this->userEmail = $userEmail;
        $this->amount = $amount;
    }

    public function handle()
    {
        $mailable = new BookingConfirmedMail($this->booking, $this->userEmail, $this->amount);
        Mail::to($this->userEmail)->send($mailable);
    }
}
