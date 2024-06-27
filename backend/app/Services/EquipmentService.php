<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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

    public function setEquipment(array $requestData, int $addressId)
    {
        try {
            $address = Address::findOrFail($addressId);

            if (Gate::denies('update', $address)) {
                abort(403, 'You are not authorized to update this address.');
            }

            $equipment = Equipment::create([
                'equipment_type_id' => $requestData['equipment_type_id'],
                'name' => $requestData['name'],
                'description' => $requestData['description'] ?? null,
            ]);

            $address->equipments()->attach($equipment->id);

            $address->load('equipments');
            return $address->equipments;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address not found.");
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public function getEquipmentType(): Collection
    {
        try {
            $equipmentTypes = EquipmentType::all();

            if ($equipmentTypes->isEmpty()) {
                throw new ModelNotFoundException("No equipment types found.");
            }

            return $equipmentTypes;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("No equipment types found.");
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve equipment.");
        }
    }


    public function deleteEquipment(int $equipmentId, int $addressId)
    {
        try {
            $address = Address::findOrFail($addressId);

            if (Gate::denies('update', $address)) {
                throw new Exception('You are not authorized to update this address.');
            }

            // Удаление связи оборудования с адресом
            $address->equipments()->detach($equipmentId);

            // Проверка, используется ли оборудование еще где-либо
            $equipmentInUse = Address::whereHas('equipments', function ($query) use ($equipmentId) {
                $query->where('equipment_id', $equipmentId);
            })->exists();

            // Если оборудование не используется, удалить его
            if (!$equipmentInUse) {
                Equipment::findOrFail($equipmentId)->delete();
            }

            // Возвращаем все оборудование у адреса
            $address->load('equipments');
            return $address->equipments;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Address or equipment not found.");
        } catch (Exception $e) {
            throw new Exception("Failed to delete equipment: " . $e->getMessage());
        }
    }
}