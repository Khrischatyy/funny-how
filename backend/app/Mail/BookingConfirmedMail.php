<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $paymentUrl;
    public $userEmail;
    public $amount;

    /**
     * Create a new message instance.
     *
     * @param $booking
     * @return void
     */
    public function __construct(Booking $booking, $paymentUrl, $userEmail, $amount)
    {
        $this->booking = $booking;
        $this->paymentUrl = $paymentUrl;
        $this->userEmail = $userEmail;
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
            view: 'emails.booking_confirmed',
            with: [
                'booking' => $this->booking,
                'paymentUrl' => $this->paymentUrl,
                'userEmail' => $this->userEmail,
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
