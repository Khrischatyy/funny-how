<?php

namespace App\Jobs;

use App\Mail\BookingConfirmedOwnerMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;

class BookingConfirmationOwnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $studioOwner;
    protected $amount;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Booking $booking
     * @param \App\Models\User $studioOwner
     * @param float|null $amount
     */
    public function __construct(Booking $booking, $studioOwner, $amount)
    {
        $this->booking = $booking;
        $this->studioOwner = $studioOwner;
        $this->amount = $amount;
    }

    public function handle()
    {
        $ownerEmail = $this->studioOwner->email;
        $mailable = new BookingConfirmedOwnerMail($this->booking, $this->studioOwner, $this->amount);
        Mail::to('rushadaev@gmail.com')->send($mailable);
    }
}
