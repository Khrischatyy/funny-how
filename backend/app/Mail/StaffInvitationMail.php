<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetLink;
    public string $role;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $resetLink, string $role)
    {
        $this->user = $user;
        $this->resetLink = $resetLink;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Invitation to Join as ' . ucfirst($this->role),
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
            view: 'emails.staff_invitation',
            with: [
                'user' => $this->user,
                'resetLink' => $this->resetLink,
                'role' => $this->role,
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