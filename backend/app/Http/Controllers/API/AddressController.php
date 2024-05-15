<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\Country;
use App\Repositories\AddressRepository;
use App\Services\CityService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

class AddressController extends BaseController
{
    public function __construct(public AddressRepository $addressRepository,
                                public CityService $cityService,
                                public CompanyService $companyService)
    {}

    public function getAddressByCityId(int $cityId): \Illuminate\Http\JsonResponse
    {
        $addresses = $this->addressRepository->getAddressByCityId($cityId)->get();

        return $this->sendResponse($addresses, 'Cities received');
    }

    public function getAddressByCompanyId(int $companyId): JsonResponse
    {
        $address = $this->addressRepository->getAddressByCompanyId($companyId);

        return $this->sendResponse($address, 'Address received');
    }

    public function createBrand(AddressRequest $addressRequest): JsonResponse
    {

        dd('s0');
        $city = $this->cityService->findOrCreateCity($addressRequest->city, $addressRequest->country);

        $company = $this->companyService->createNewCompany($addressRequest);

        $address = $this->createAddress($addressRequest, $city, $company);

        return $this->sendResponse($company, 'Brand added');
    }

    public function createAddress(AddressRequest $addressRequest, $city, $company)
    {
        return Address::create([
            'street' => $addressRequest->street,
            'longitude' => $addressRequest->longitude,
            'latitude' => $addressRequest->latitude,
            'city_id' => $city->id,
            'company_id' => $company->id,
        ]);
    }
}
