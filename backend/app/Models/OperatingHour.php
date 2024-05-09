<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingHour extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['day', 'open_time', 'close_time', 'address_id', 'mode_id', 'day_of_week'];
}
