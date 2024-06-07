<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Repositories\CountryRepository;

class CountryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/countries",
     *     tags={"Find By Place"},
     *     summary="Get list of countries",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Serbia")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Countries received"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function getCountries(CountryRepository $countryRepository): \Illuminate\Http\JsonResponse
    {
        $countries = $countryRepository->all();

        return $this->sendResponse($countries, 'Countries received');
    }
}
