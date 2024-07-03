<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingHour extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['day', 'open_time', 'close_time', 'address_id', 'mode_id', 'day_of_week'];

    public function getOpenTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getCloseTimeAttribute($value)
    {
        return $value === '23:59:59' ? '24:00' : Carbon::parse($value)->format('H:i');
    }
}
