<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EquipmentService
{
    public function getEquipmentsByAddressId(int $addressId)
    {
        try {
            $address = Address::findOrFail($addressId);
            return $address->equipments()->with('type')->get(); // Загрузка типа оборудования
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve equipment.");
        }
    }

    public function setEquipment(array $equipment, int $addressId)
    {
        try {
            $address = Address::findOrFail($addressId);
            $address->equipments()->attach($equipment['equipment_id']);
            $address->load('equipments'); // Загружаем все связанные оборудования
            return $address->equipments;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        } catch (Exception $e) {
            throw new Exception("Failed to set equipment.");
        }
    }

    public function deleteEquipment(int $equipmentId, int $addressId)
    {
        try {
            $address = Address::findOrFail($addressId);
            $address->equipments()->detach($equipmentId);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        } catch (Exception $e) {
            throw new Exception("Failed to delete equipment.");
        }
    }
}