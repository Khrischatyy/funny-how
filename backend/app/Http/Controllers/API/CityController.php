<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Repositories\CityRepository;

class CityController extends BaseController
{
    public function __construct(public CityRepository $cityRepository)
    {}

    /**
     * @OA\Get(
     *     path="/countries/{country_id}/cities",
     *     tags={"Find By Place"},
     *     summary="Get list of cities by country ID",
     *     @OA\Parameter(
     *         name="country_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the country"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Cities received",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="belgrade"),
     *                     @OA\Property(property="country_id", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Cities received"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function getCitiesByCountryId($countryId): \Illuminate\Http\JsonResponse
    {
        $cities = $this->cityRepository->getCitiesByCountryId($countryId)->get();

        return $this->sendResponse($cities, 'Cities received');
    }
}
