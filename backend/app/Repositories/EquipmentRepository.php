<?php

namespace App\Repositories;

use App\Models\Address;
use App\Repositories\Interfaces\EquipmentRepositoryInterface;

class EquipmentRepository implements EquipmentRepositoryInterface
{
    public function getEquipmentsByAddressId(int $addressId)
    {
        return Address::whereId($addressId)->with(['company', 'equipment'])->first();
    }
}
