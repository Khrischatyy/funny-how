<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;

class BookingConfirmedOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $studioOwner;
    public $amount;

    /**
     * Create a new message instance.
     *
     * @param $booking
     * @return void
     */
    public function __construct(Booking $booking, User $studioOwner, $amount)
    {
        $this->booking = $booking;
        $this->studioOwner = $studioOwner;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Booking Confirmed Mail',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.booking_confirmed_owner',
            with: [
                'booking' => $this->booking,
                'studioOwner' => $this->studioOwner,
                'amount' => $this->amount,
                'user' => $this->booking->user,
            ],
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
