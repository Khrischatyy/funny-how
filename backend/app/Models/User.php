<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
}
