<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\EquipmentService;

class EquipmentController extends BaseController
{
    public function __construct(public EquipmentService $equipmentService)
    {}

    public function getEquipmentsByAddressId(int $addressId)
    {
        $equipments = $this->equipmentService->getEquipmentsByAddressId($addressId);

        return $this->sendResponse($equipments, 'Equipments received');
    }
}
