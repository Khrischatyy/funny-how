<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddressPhotosRequest;
use App\Http\Requests\AddressPriceDeleteRequest;
use App\Http\Requests\AddressPricesRequest;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Models\Address;
use App\Models\AddressPrice;
use App\Models\AdminCompany;
use App\Models\Company;
use App\Repositories\AddressRepository;
use App\Services\AddressService;
use App\Services\CityService;
use App\Services\CompanyService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AddressController extends BaseController
{
    public function __construct(public AddressRepository $addressRepository,
                                public AddressService $addressService,
                                public CityService $cityService,
                                public CompanyService $companyService)
    {}

    /**
     * @OA\Get(
     *     path="/address/{address_id}",
     *     summary="Get an address by its ID",
     *     tags={"Address"},
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
     *         description="Address retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="street", type="string", example="Main St"),
     *                 @OA\Property(property="city_id", type="integer", example=100),
     *                 @OA\Property(property="company_id", type="integer", example=10),
     *                 @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                 @OA\Property(property="longitude", type="string", example="44.792424"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Address retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found")
     * )
     */
    public function getAddressById(int $addressId): JsonResponse
    {
        try {
            $address = $this->addressService->getAddressById($addressId);
            return $this->sendResponse($address, 'Address retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve address.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/address/{address_id}/photos",
     *     summary="Upload photos for an address",
     *     tags={"Address"},
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
     *             @OA\Property(property="photos", type="array", @OA\Items(type="string", format="binary")),
     *             @OA\Property(property="index", type="string", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Photos uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="path", type="string", example="photos/1.jpg"),
     *                     @OA\Property(property="address_id", type="integer", example=1),
     *                     @OA\Property(property="index", type="string", example="1")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Photos uploaded successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to upload photos")
     * )
     */
    public function uploadAddressPhotos(AddressPhotosRequest $request): JsonResponse
    {
        $address_id = (int) $request->input('address_id');

        try {
            $result = $this->addressService->uploadAddressPhotos($request, $address_id);

            return $this->sendResponse($result['photos'], 'Photos uploaded successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to upload photos.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/city/{city_id}/studios",
     *     tags={"Find By Place"},
     *     summary="Get list of studios by city ID",
     *     @OA\Parameter(
     *         name="city_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the city"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Addresses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                     @OA\Property(property="longitude", type="string", example="44.792424"),
     *                     @OA\Property(property="street", type="string", example="Mirijevski Venac 4"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                     @OA\Property(property="city_id", type="integer", example=1),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="company", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Section"),
     *                         @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                         @OA\Property(property="slug", type="string", example="section"),
     *                         @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                         @OA\Property(property="rating", type="string", example="9.7"),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="badges", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="rent"),
     *                             @OA\Property(property="image", type="string", example="public/badges/rent.svg"),
     *                             @OA\Property(property="pivot", type="object",
     *                                 @OA\Property(property="address_id", type="integer", example=2),
     *                                 @OA\Property(property="badge_id", type="integer", example=3)
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Addresses retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No addresses found in the specified city"),
     *     @OA\Response(response="500", description="Failed to retrieve addresses")
     * )
     */
    public function getAddressesInCity(int $cityId): JsonResponse
    {
        try {
            $addresses = $this->addressService->getAddressByCityIdWithWorkingHours($cityId);

            if ($addresses->isEmpty()) {
                return $this->sendError('No addresses found in the specified city.', 404);
            }

            return $this->sendResponse($addresses, 'Addresses retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve addresses.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/brand",
     *     summary="Create a new brand (company and address)",
     *     tags={"Brand"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"street", "city", "country", "longitude", "latitude", "company"},
     *             @OA\Property(property="street", type="string", example="Газетный переулок"),
     *             @OA\Property(property="city", type="string", example="Москва"),
     *             @OA\Property(property="country", type="string", example="Россия"),
     *             @OA\Property(property="longitude", type="number", format="float", example=37.609337),
     *             @OA\Property(property="latitude", type="number", format="float", example=55.758972),
     *             @OA\Property(property="logo", type="string", format="binary", description="Company logo"),
     *             @OA\Property(property="company", type="string", example="Section")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Company and address added",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="name", type="string", example="Sectionss"),
     *                 @OA\Property(property="logo", type="string", nullable=true, example=null),
     *                 @OA\Property(property="slug", type="string", example="sectionss"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-07T10:22:34.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-07T10:22:34.000000Z"),
     *                 @OA\Property(property="id", type="integer", example=3)
     *             ),
     *             @OA\Property(property="message", type="string", example="Company and address added"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="422", description="Unprocessable Entity")
     * )
     */
    public function createBrand(AddressRequest $addressRequest): JsonResponse
    {

        $city = $this->cityService->findOrCreateCity($addressRequest->city, $addressRequest->country);

        $company = $this->companyService->createNewCompany($addressRequest);

        $address = $this->addressService->createAddress($addressRequest, $city, $company);

        $this->addDefaultHours($address->id);

        // Return the company and address_id

       return $this->sendResponse([
            'slug' => $company->slug,
            'address_id' => $address->id
        ], 'Company and address added');
    }

    /**
     * @OA\Get(
     *     path="/address/{address_id}/prices",
     *     summary="Get prices for an address",
     *     tags={"Prices"},
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
     *         description="Prices updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="hours", type="integer", example=1),
     *                     @OA\Property(property="total_price", type="number", format="float", example=60.00),
     *                     @OA\Property(property="price_per_hour", type="number", format="float", example=60.00),
     *                     @OA\Property(property="is_enabled", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Prices updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to retrieve prices")
     * )
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
     * @OA\Post(
     *     path="/address/{address_id}/prices",
     *     summary="Create or update prices for an address",
     *     tags={"Prices"},
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
     *             required={"hours", "total_price", "is_enabled"},
     *             @OA\Property(property="hours", type="integer", example=1),
     *             @OA\Property(property="total_price", type="number", format="float", example=60.00),
     *             @OA\Property(property="is_enabled", type="boolean", example=true),
     *             @OA\Property(property="address_price_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Prices updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="address_id", type="integer", example=1),
     *                     @OA\Property(property="hours", type="integer", example=1),
     *                     @OA\Property(property="is_enabled", type="boolean", example=true),
     *                     @OA\Property(property="total_price", type="string", example="50.00"),
     *                     @OA\Property(property="price_per_hour", type="string", example="50.00")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Prices updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address or price entry not found"),
     *     @OA\Response(response="500", description="Failed to update prices")
     * )
     */
    public function createOrUpdateAddressPrice(AddressPricesRequest $request, int $address_id): JsonResponse
    {
        $hours = $request->input('hours');
        $total_price = $request->input('total_price');
        $is_enabled = $request->input('is_enabled');

        $address_price_id = $request->input('address_price_id');

        try {
            $address = Address::findOrFail($address_id);

            // Calculate price per hour
            $price_per_hour = $total_price / $hours;

            if ($address_price_id) {
                // Find the existing price entry
                $price_entry = $address->prices()->findOrFail($address_price_id);

                // Update the existing price entry
                $price_entry->update([
                    'hours' => $hours,
                    'total_price' => $total_price,
                    'price_per_hour' => $price_per_hour,
                    'is_enabled' => $is_enabled,
                ]);
            } else {
                // Create the new price entry
                $address->prices()->create([
                    'address_id' => $address_id,
                    'hours' => $hours,
                    'total_price' => $total_price,
                    'price_per_hour' => $price_per_hour,
                    'is_enabled' => $is_enabled,
                ]);
            }

            return $this->sendResponse($address->prices, 'Studio prices updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address or price entry not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update studio prices.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/address/prices",
     *     summary="Delete a price for an address",
     *     tags={"Prices"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "address_prices_id"},
     *             @OA\Property(property="address_id", type="integer", example=1),
     *             @OA\Property(property="address_prices_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Price deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="address_id", type="integer", example=1),
     *                     @OA\Property(property="hours", type="integer", example=4),
     *                     @OA\Property(property="is_enabled", type="boolean", example=true),
     *                     @OA\Property(property="total_price", type="string", example="80.00"),
     *                     @OA\Property(property="price_per_hour", type="string", example="20.00")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Studio price deleted successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Price not found for the given address"),
     *     @OA\Response(response="500", description="Failed to delete price")
     * )
     */
    public function deleteAddressPrices(AddressPriceDeleteRequest $request): JsonResponse
    {
        $address_id = $request->input('address_id');
        $address_price_id = $request->input('address_prices_id');


        try {
            $price = AddressPrice::with(['company'])->where('address_id', $address_id)->where('id', $address_price_id)->firstOrFail();
            $price->delete();

            // Fetch the updated list of prices for the address
            $prices = Address::with('prices')->findOrFail($address_id)->prices;

            return $this->sendResponse($prices, 'Studio price deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Price not found for the given address.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete studio price.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-studios",
     *     tags={"Studios"},
     *     summary="Get list of my studios",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Studios retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                     @OA\Property(property="longitude", type="string", example="44.792424"),
     *                     @OA\Property(property="street", type="string", example="Mirijevski Venac 4"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                     @OA\Property(property="city_id", type="integer", example=1),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="company", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Section"),
     *                         @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                         @OA\Property(property="slug", type="string", example="section"),
     *                         @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                         @OA\Property(property="rating", type="string", example="9.7"),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="badges", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="rent"),
     *                             @OA\Property(property="image", type="string", example="public/badges/rent.svg"),
     *                             @OA\Property(property="pivot", type="object",
     *                                 @OA\Property(property="address_id", type="integer", example=2),
     *                                 @OA\Property(property="badge_id", type="integer", example=3)
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Studios retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No studios found"),
     *     @OA\Response(response="500", description="Failed to retrieve studios")
     * )
     */
    public function getMyAddresses(): JsonResponse
    {
        try {
            $addresses = $this->addressService->getMyAddresses();

            if ($addresses->isEmpty()) {
                return $this->sendError('No studios found.', 404);
            }

            return $this->sendResponse($addresses, 'Studios retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve studios.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/{slug}/addresses",
     *     summary="Get addresses by company slug",
     *     tags={"Addresses"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The slug of the company"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Addresses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="street", type="string", example="Gazetny pereulok"),
     *                     @OA\Property(property="city_id", type="integer", example=1),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="longitude", type="string", example="37.609337"),
     *                     @OA\Property(property="latitude", type="string", example="55.758972"),
     *                     @OA\Property(property="badges", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Rent"),
     *                             @OA\Property(property="image", type="string", example="url_to_image")
     *                         )
     *                     ),
     *                     @OA\Property(property="working_hours", type="object",
     *                         @OA\Property(property="day", type="string", example="Monday"),
     *                         @OA\Property(property="open", type="string", example="09:00"),
     *                         @OA\Property(property="close", type="string", example="18:00")
     *                     ),
     *                     @OA\Property(property="prices", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="hours", type="integer", example=1),
     *                             @OA\Property(property="total_price", type="number", example=60.00),
     *                             @OA\Property(property="price_per_hour", type="number", example=60.00),
     *                             @OA\Property(property="is_enabled", type="boolean", example=true)
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Addresses retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Company not found"),
     *     @OA\Response(response="500", description="Failed to retrieve addresses")
     * )
     */
    public function getAddressesByCompanySlug(string $slug): JsonResponse
    {
        try {
            $addresses = $this->addressService->getAddressesByCompanySlug($slug);
            return $this->sendResponse($addresses, 'Addresses retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve addresses.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Patch(
     *     path="/photos/update-index",
     *     summary="Update the index of a photo",
     *     tags={"Photos"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"photo_id", "index"},
     *             @OA\Property(property="photo_id", type="integer", example=1, description="The ID of the photo"),
     *             @OA\Property(property="index", type="integer", example=1, description="New index for the photo")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Photo index updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="path", type="string", example="photos/1.jpg"),
     *                 @OA\Property(property="address_id", type="integer", example=1),
     *                 @OA\Property(property="index", type="integer", example=1)
     *             ),
     *             @OA\Property(property="message", type="string", example="Photo index updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Photo not found"),
     *     @OA\Response(response="500", description="Failed to update photo index")
     * )
     */
    public function updatePhotoIndex(UpdatePhotoIndexRequest $request): JsonResponse
    {
        $photo_id = $request->input('address_photo_id');

        try {
            $result = $this->addressService->updatePhotoIndex($request, $photo_id);

            return $this->sendResponse($result, 'Photo index updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Photo not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update photo index.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/map/studios",
     *     summary="Get all studios",
     *     tags={"Map"},
     *     @OA\Response(
     *         response="200",
     *         description="Studios retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="street", type="string", example="Main St"),
     *                     @OA\Property(property="city_id", type="integer", example=100),
     *                     @OA\Property(property="company_id", type="integer", example=10),
     *                     @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                     @OA\Property(property="longitude", type="string", example="44.792424"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Studios retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="500", description="Failed to retrieve studios")
     * )
     */
    public function getAllStudios(): JsonResponse
    {
        try {
            $studios = $this->addressService->getAllStudios();
            return $this->sendResponse($studios, 'Studios retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve studios.', 500, ['error' => $e->getMessage()]);
        }
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
}
