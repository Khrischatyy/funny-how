<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudioClosure extends Model
{
    use HasFactory;

    protected $fillable = ['studio_id', 'closure_date', 'reason'];

    /**
     * Получить студию, связанную с закрытием.
     */
    public function studio()
    {
        return $this->belongsTo(Address::class, 'id');
    }
}