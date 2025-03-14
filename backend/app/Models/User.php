<?php

namespace App\Models;

use App\Jobs\SendVerifyEmailJob;
use App\Mail\CustomVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'profile_photo',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'google_id',
        'stripe_account_id',
        'payment_gateway',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Связь с моделью AdminCompany
    public function adminCompany()
    {
        return $this->hasOne(AdminCompany::class, 'admin_id');
    }

    public function company()
    {
        return $this->hasOneThrough(Company::class, AdminCompany::class, 'admin_id', 'id', 'id', 'company_id');
    }

    // Связь с моделью Booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function roleName()
    {
        return $this->getRoleNames()->first();
    }

    public function engineerRate()
    {
        return $this->hasOne(EngineerRate::class);
    }

    public function sendEmailVerificationNotification()
    {
        // Генерация URL для верификации email
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $this->id, 'hash' => sha1($this->email)]
        );

        // Отправка кастомного письма
        SendVerifyEmailJob::dispatch($this, $verificationUrl);
    }

    //к каким адресам привязан инженер
    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'engineer_addresses');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
