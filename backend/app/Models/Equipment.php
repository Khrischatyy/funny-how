<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'equipment_type_id'];

    /**
     * Get the type of equipment.
     */
    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id', 'id');
    }

    /**
     * Get the addresses that have this equipment.
     */
    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_equipment', 'equipment_id', 'address_id')
            ->withPivot('address_id', 'equipment_id');
    }
}