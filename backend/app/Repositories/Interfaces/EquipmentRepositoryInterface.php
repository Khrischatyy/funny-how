<?php

namespace App\Repositories\Interfaces;

interface EquipmentRepositoryInterface
{
    public function getEquipmentsByAddressId(int $addressId);
}
