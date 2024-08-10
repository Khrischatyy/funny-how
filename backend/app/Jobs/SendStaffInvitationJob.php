<?php

namespace App\Jobs;

use App\Mail\StaffInvitationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendStaffInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public string $resetLink;
    public string $role;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new StaffInvitationMail($this->user, $this->resetLink, $this->role));
    }
}
