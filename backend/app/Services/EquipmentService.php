<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\EquipmentRepository;

class EquipmentService
{
    public function __construct(public EquipmentRepository $equipmentRepository)
    {}

    public function getEquipmentsByAddressId(int $addressId)
    {
        return $this->equipmentRepository->getEquipmentsByAddressId($addressId);
    }
}
