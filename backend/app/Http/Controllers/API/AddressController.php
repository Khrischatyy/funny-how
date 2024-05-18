<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddressPriceDeleteRequest;
use App\Http\Requests\AddressPricesRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\AddressPrice;
use App\Models\Country;
use App\Repositories\AddressRepository;
use App\Services\CityService;
use App\Services\CompanyService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

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

        $city = $this->cityService->findOrCreateCity($addressRequest->city, $addressRequest->country);

        $company = $this->companyService->createNewCompany($addressRequest);

        $address = $this->createAddress($addressRequest, $city, $company);

        $this->addDefaultHours($address->id);

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

    /**
     * Add a default price/hours (1hour/60$) to address
     *
     * @param int $address_id
     * @return Address | JsonResponse
     */
    private function addDefaultHours(int $address_id): Address | JsonResponse
    {
        try {
            $address = Address::findOrFail($address_id);

            // Set price for 1 hour at $60
            $address->prices()->create([
                'address_id' => $address_id,
                'hours' => 1,
                'total_price' => 60.00,
                'price_per_hour' => 60.00
            ]);

            return $address;
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to add price.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getAddressBadges($address_id): JsonResponse
    {
        try {
            // Find the address
            $address = Address::with('badges')->findOrFail($address_id);

            // Generate the S3 URLs for the badges
            $badges = $address->badges->map(function ($badge) {
                $badge->image_url = Storage::disk('s3')->url($badge->image);
                return $badge;
            });



            return $this->sendResponse($badges, 'Badges retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve badges.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get studio prices for a specific address.
     *
     * @param int $address_id
     * @return JsonResponse
     */
    public function getAddressPrices($address_id): JsonResponse
    {
        try {
            $address = Address::with('prices')->findOrFail($address_id);
            return $this->sendResponse($address->prices, 'Studio prices retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve studio prices.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update studio prices for a specific address.
     *
     * @param AddressPricesRequest $request
     * @return JsonResponse
     */
    public function updateAddressStudioPrices(AddressPricesRequest $request, int $address_id): JsonResponse
    {
        $hours = $request->input('hours');
        $total_price = $request->input('total_price');

        try {
            $address = Address::findOrFail($address_id);

            // Calculate price per hour
            $price_per_hour = $total_price / $hours;

            // Create the price entry with the calculated price_per_hour
            $address->prices()->create([
                'address_id' => $address_id,
                'hours' => $hours,
                'total_price' => $total_price,
                'price_per_hour' => $price_per_hour
            ]);

            return $this->sendResponse($address->prices, 'Studio prices updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update studio prices.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove studio prices for a specific address.
     *
     * @param int $price_id
     * @return JsonResponse
     */
    public function deleteAddressPrices(AddressPriceDeleteRequest $request): JsonResponse
    {
        $address_id = $request->input('address_id');
        $address_price_id = $request->input('address_prices_id');

        try {
            $address = Address::with('prices')->findOrFail($address_id);
            $price = AddressPrice::where('address_id', $address_id)->where('id', $address_price_id)->firstOrFail();
            $price->delete();

            return $this->sendResponse($address->prices, 'Studio price deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Price not found for the given address.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete studio price.', 500, ['error' => $e->getMessage()]);
        }
    }
}
