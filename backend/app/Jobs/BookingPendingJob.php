<?php

namespace App\Jobs;

use App\Mail\BookingPendingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;

class BookingPendingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $paymentUrl;
    protected $userEmail;
    protected $amount;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Booking $booking
     * @param string|null $paymentUrl
     * @param string $userEmail
     * @param float|null $amount
     */
    public function __construct(Booking $booking, $paymentUrl, $userEmail, $amount)
    {
        $this->booking = $booking;
        $this->paymentUrl = $paymentUrl;
        $this->userEmail = $userEmail;
        $this->amount = $amount;
    }

    public function handle()
    {
        $mailable = new BookingPendingMail($this->booking, $this->paymentUrl, $this->userEmail, $this->amount);
        Mail::to($this->userEmail)->send($mailable);
    }
}
