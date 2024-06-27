<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\EquipmentRequest;
use App\Services\EquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EquipmentController extends BaseController
{
    public function __construct(public EquipmentService $equipmentService)
    {}

    /**
     * @OA\Get(
     *     path="/address/{address_id}/equipment",
     *     summary="Get equipment for an address",
     *     tags={"Equipment"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Equipments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="soyuz 017 serial"),
     *                     @OA\Property(property="description", type="string", example="Awesome as fuck micro. Fantastic for hip-hop"),
     *                     @OA\Property(property="shop_path", type="string", example="amazon.com/micro/soyuz017"),
     *                     @OA\Property(property="equipment_type_id", type="integer", example=1),
     *                     @OA\Property(property="equipment_type_name", type="string", example="Microphone"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-25T11:06:42.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-25T11:06:42.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Equipments retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to retrieve equipment")
     * )
     */
    public function getEquipmentsByAddressId(int $addressId): JsonResponse
    {
        try {
            $equipments = $this->equipmentService->getEquipmentsByAddressId($addressId);
            return $this->sendResponse($equipments, 'Equipments retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve equipment.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/address/{address_id}/equipment",
     *     summary="Set equipment for an address",
     *     tags={"Equipment"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"equipment_id"},
     *             @OA\Property(property="equipment_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Equipment added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Equipment added successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to add equipment")
     * )
     */
    public function setEquipment(EquipmentRequest $request, int $addressId): JsonResponse
    {
        try {
            $equipments = $this->equipmentService->setEquipment($request->validated(), $addressId);
            return $this->sendResponse($equipments, 'Equipment added successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to add equipment.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/address/{address_id}/equipment",
     *     summary="Delete equipment from an address",
     *     tags={"Equipment"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"equipment_id"},
     *             @OA\Property(property="equipment_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Equipment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Equipment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to delete equipment")
     * )
     */
    public function deleteEquipment(Request $request, int $addressId): JsonResponse
    {
        try {
            $this->equipmentService->deleteEquipment($request->input('equipment_id'), $addressId);
            return $this->sendResponse(null, 'Equipment deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete equipment.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getEquipmentType($address_id)
    {
        try {
            $equipmentTypes = $this->equipmentService->getEquipmentTypeByAddressId($address_id);

            if (empty($equipmentTypes)) {
                return $this->sendError('No equipment types found for the given address', 404);
            }

            return $this->sendResponse($equipmentTypes, 'Equipment types retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Server Error', 500, [$e->getMessage()]);
        }
    }

}