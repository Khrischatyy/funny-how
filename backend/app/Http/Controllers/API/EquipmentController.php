<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\EquipmentService;

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
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="latitude", type="string", example="37.609337"),
     *                 @OA\Property(property="longitude", type="string", example="55.758972"),
     *                 @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="city_id", type="integer", example=2),
     *                 @OA\Property(property="company_id", type="integer", example=1),
     *                 @OA\Property(property="company", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Section"),
     *                     @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                     @OA\Property(property="slug", type="string", example="section"),
     *                     @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                     @OA\Property(property="rating", type="string", example="9.7"),
     *                     @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                     @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                 ),
     *                 @OA\Property(property="equipment", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="soyuz 017 serial"),
     *                         @OA\Property(property="description", type="string", example="Awesome as fuck micro. Fantastic for hip-hop"),
     *                         @OA\Property(property="shop_path", type="string", example="amazon.com/micro/soyuz017"),
     *                         @OA\Property(property="type_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                         @OA\Property(property="pivot", type="object",
     *                             @OA\Property(property="address_id", type="integer", example=1),
     *                             @OA\Property(property="equipment_id", type="integer", example=1)
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Equipments received"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to retrieve equipment")
     * )
     */
    public function getEquipmentsByAddressId(int $addressId)
    {
        $equipments = $this->equipmentService->getEquipmentsByAddressId($addressId);

        return $this->sendResponse($equipments, 'Equipments received');
    }
}
