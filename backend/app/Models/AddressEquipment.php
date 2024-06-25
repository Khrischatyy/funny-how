<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressEquipment extends Model
{
    use HasFactory;

    protected $table = 'address_equipment';

    protected $fillable = ['address_id', 'equipment_id'];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
