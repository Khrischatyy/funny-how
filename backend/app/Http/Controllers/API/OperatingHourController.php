<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\OperatingHourRequest;
use App\Services\CompanyService;
use App\Services\OperatingHourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OperatingHourController extends BaseController
{
    public function __construct(public OperatingHourService $operatingHourService, public CompanyService $companyService)
    {}

    /**
     * @OA\Get(
     *     path="/operation-modes",
     *     summary="Get operation modes",
     *     tags={"Operating Hours"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Operation modes received",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="mode", type="string", example="24/7"),
     *                     @OA\Property(property="description", type="string", example="24/7 - Работает каждый день, рабочие часы проставлять не нужно,\n                     все остальные (everyday, weekdays,holidays) - недоступны")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Operation modes received"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="500", description="Failed to retrieve operation modes")
     * )
     */
    public function getOperationModes(): JsonResponse
    {
        $operationModes = DB::table('operating_modes')->get();

        return $this->sendResponse($operationModes, 'Operation modes received');
    }

    /**
     * @OA\Post(
     *     path="/address/operating-hours",
     *     summary="Set operating hours for an address",
     *     tags={"Operating Hours"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mode_id", type="integer", example=3, description="Mode of operation: 1 for permanent, 2 for everyday, 3 for regular"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="ID of the address"),
     *             @OA\Property(property="is_closed", type="boolean", example=false, description="Whether the address is closed"),
     *             @OA\Property(property="day_of_week", type="integer", example=1, description="Day of the week, required if setting specific day (0 for Sunday, 6 for Saturday)"),
     *
     *             @OA\Property(
     *                 property="open_time",
     *                 type="string",
     *                 format="time",
     *                 example="09:00",
     *                 description="Opening time (required if mode_id is 2 or 3)"
     *             ),
     *             @OA\Property(
     *                 property="close_time",
     *                 type="string",
     *                 format="time",
     *                 example="18:00",
     *                 description="Closing time (required if mode_id is 2 or 3)"
     *             ),
     *             @OA\Property(
     *                 property="open_time_weekend",
     *                 type="string",
     *                 format="time",
     *                 example="10:00",
     *                 description="Weekend opening time (required if mode_id is 3)"
     *             ),
     *             @OA\Property(
     *                 property="close_time_weekend",
     *                 type="string",
     *                 format="time",
     *                 example="16:00",
     *                 description="Weekend closing time (required if mode_id is 3)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Hours were added",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="address_id", type="integer", example=1),
     *                     @OA\Property(property="mode_id", type="integer", example=4),
     *                     @OA\Property(property="day_of_week", type="integer", example=1),
     *                     @OA\Property(property="open_time", type="string", example="09:00"),
     *                     @OA\Property(property="close_time", type="string", example="18:00")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Hours were added"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function setOperatingHours(OperatingHourRequest $operatingHourRequest): JsonResponse
    {
        $address_id = $operatingHourRequest->input('address_id');
        $mode = (int) $operatingHourRequest->input('mode_id');
        $open_time = $operatingHourRequest->input('open_time');
        $close_time = $operatingHourRequest->input('close_time');

        $company = $this->companyService->getCompanyByAddressId($address_id);

        //проверка может ли studio_owner апдейтить студию
        $this->authorize('update', $company);


        $open_time_weekend = $operatingHourRequest->input('open_time_weekend', null);
        $close_time_weekend = $operatingHourRequest->input('close_time_weekend', null);

        $hours = match ($mode) {
            1 => $this->operatingHourService->permanent($address_id, $mode),
            2 => $this->operatingHourService->everyday($address_id, $mode, $open_time, $close_time),
            3 => $this->operatingHourService->regular($address_id, $open_time, $close_time, $open_time_weekend, $close_time_weekend),
//            4 => $this->operatingHourService->specificDay(),
        };

        return $this->sendResponse($hours, 'Hours were added');
    }
}
