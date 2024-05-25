<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCompany extends Model
{
    use HasFactory;

    protected $table = 'admin_company';

    protected $fillable = ['admin_id', 'company_id'];

    // Связь с моделью User
    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Связь с моделью Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}

