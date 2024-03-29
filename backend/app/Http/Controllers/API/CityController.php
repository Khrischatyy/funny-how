<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Repositories\CityRepository;

class CityController extends BaseController
{
    public function __construct(public CityRepository $cityRepository)
    {}

    public function getCitiesByCountryId($countryId): \Illuminate\Http\JsonResponse
    {
        $cities = $this->cityRepository->getCitiesByCountryId($countryId)->get();

        return $this->sendResponse($cities, 'Cities received');
    }
}
