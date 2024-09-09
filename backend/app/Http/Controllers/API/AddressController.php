<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\BrandRequest;
use App\Http\Requests\DeleteAddressRequest;
use App\Http\Requests\FilterAddressRequest;
use App\Http\Requests\ToggleFavoriteStudioRequest;
use App\Http\Requests\UpdatePhotoIndexRequest;
use App\Http\Requests\UpdateSlugRequest;
use App\Models\Address;
use App\Models\Room;
use App\Repositories\AddressRepository;
use App\Services\AddressService;
use App\Services\RoomService;
use App\Services\CityService;
use App\Services\CompanyService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressController extends BaseController
{
    public function __construct(public AddressRepository $addressRepository,
                                public AddressService $addressService,
                                public RoomService $roomService,
                                public CityService $cityService,
                                public CompanyService $companyService)
    {}

    /**
     * @OA\Get(
     *     path="/studio/{address_slug}",
     *     summary="Get an address by its slug",
     *     tags={"Address"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The slug of the address"
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
     *                 @OA\Property(property="slug", type="string", example="company-name-city-name"),
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
    public function getAddressBySlug($addressSlug): JsonResponse
    {
        try {
            $address = $this->addressService->getAddressBySlug($addressSlug);

            return $this->sendResponse($address, 'Address retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve address.', 500, ['error' => $e->getMessage()]);
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
    public function createBrand(BrandRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->hasRole('studio_owner')) {
            throw new \Exception('You are not studio owner');
        }

        $city = $this->cityService->findOrCreateCity($request->city, $request->country);

        $company = $this->companyService->createNewCompany($request);

        $address = $this->addressService->createAddress($request, $city, $company);

        $room = $this->addDefaultRoom($address);

        $this->addDefaultHours($address->id);

        return $this->sendResponse([
            'slug' => $company->slug,
            'address_id' => $address->id,
            'room_id' => $room->id
        ], 'Company and address added');
    }

    /**
     * @OA\Post(
     *     path="/address",
     *     summary="Create a new address",
     *     tags={"Address"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"street", "city_id", "company_id", "latitude", "longitude"},
     *             @OA\Property(property="street", type="string", example="Main St"),
     *             @OA\Property(property="city_id", type="integer", example=100),
     *             @OA\Property(property="company_id", type="integer", example=10),
     *             @OA\Property(property="latitude", type="string", example="20.5320636"),
     *             @OA\Property(property="longitude", type="string", example="44.792424")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Address created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="street", type="string", example="Main St"),
     *                 @OA\Property(property="city_id", type="integer", example=100),
     *                 @OA\Property(property="company_id", type="integer", example=10),
     *                 @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                 @OA\Property(property="longitude", type="string", example="44.792424"),
     *                 @OA\Property(property="slug", type="string", example="company-name-city-name-1"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Address created successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Failed to create address")
     * )
     */
    public function createAddress(AddressRequest $request): JsonResponse
    {
        try {
            $city = $this->cityService->findOrCreateCity($request->city, $request->country);
            $company = $this->companyService->getCompanyById($request->company_id);

            $this->authorize('update', $company);

            $address = $this->addressService->createAddress($request, $city, $company);
            $address->company_slug = $company->slug;

            $room = $this->addDefaultRoom($address);
            return $this->sendResponse($address, 'Address created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create address.', 500, ['error' => $e->getMessage()]);
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
     * @OA\Post(
     *     path="/delete-studio",
     *     summary="Delete an address (studio)",
     *     tags={"Address"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id"},
     *             @OA\Property(property="address_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Address deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address deleted successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to delete address")
     * )
     */
    public function deleteAddress(DeleteAddressRequest $request): JsonResponse
    {
        $address_id = $request->input('address_id');

        try {
            $this->addressService->deleteAddress($address_id);
            return $this->sendResponse([], 'Address and operating hours deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete address.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/my-studios/filter",
     *     summary="Filter addresses based on criteria",
     *     tags={"Addresses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="search", type="string", example="Studio Name"),
     *             @OA\Property(property="city", type="integer", example=1),
     *             @OA\Property(property="price", type="number", example=100),
     *             @OA\Property(property="rating", type="number", example=4.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Filtered addresses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Studio Name"),
     *                     @OA\Property(property="city", type="string", example="City Name"),
     *                     @OA\Property(property="price", type="number", example=100),
     *                     @OA\Property(property="rating", type="number", example=4.5)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Filtered addresses retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function filterMyAddresses(FilterAddressRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $company = $user->adminCompany->company;

            $this->authorize('update', $company);

            $addresses = $this->addressService->filterMyAddresses($company->id, $request->validated());

            return $this->sendResponse($addresses, 'Filtered addresses retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve addresses.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-studios/cities",
     *     summary="Get list of cities where user's studios are located",
     *     tags={"Addresses"},
     *     @OA\Response(
     *         response="200",
     *         description="Cities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="City Name")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Cities retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function getMyCities(): JsonResponse
    {
        try {
            $user = auth()->user();
            $company = $user->adminCompany->company;

            $this->authorize('update', $company);

            $cities = $this->addressService->getCitiesByCompany($company->id);

            return $this->sendResponse($cities, 'Cities retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve cities.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/user/toggle-favorite-studio",
     *     summary="Toggle favorite studio for user",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id"},
     *             @OA\Property(property="address_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Favorite studio toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="address_id", type="integer", example=1),
     *                 @OA\Property(property="favorite", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Favorite studio toggled successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function toggleFavorite(ToggleFavoriteStudioRequest $request): JsonResponse
    {
        $addressId = $request->input('address_id');
        $userId = Auth::id();

        try {
            $result = $this->addressService->toggleFavorite($userId, $addressId);
            return $this->sendResponse($result, 'Favorite status toggled successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to toggle favorite status.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/{address_id}/list",
     *     summary="Get list of addresses available to the user",
     *     tags={"Addresses"},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Addresses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="street", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="New York"),
     *                     @OA\Property(property="state", type="string", example="NY"),
     *                     @OA\Property(property="postal_code", type="string", example="10001")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Addresses retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function listAddresses(): JsonResponse
    {
        try {
            $user = Auth::user();

            $addresses = $this->addressService->listUserAddresses($user);

            return $this->sendResponse($addresses, 'Addresses retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve addresses.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/studio/{address_slug}/update-slug",
     *     summary="Update the slug of an address",
     *     tags={"Address"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The current slug of the address"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"new_slug"},
     *             @OA\Property(property="new_slug", type="string", example="new-company-name-city-name")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Slug updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="street", type="string", example="Main St"),
     *                 @OA\Property(property="city_id", type="integer", example=100),
     *                 @OA\Property(property="company_id", type="integer", example=10),
     *                 @OA\Property(property="latitude", type="string", example="20.5320636"),
     *                 @OA\Property(property="longitude", type="string", example="44.792424"),
     *                 @OA\Property(property="slug", type="string", example="new-company-name-city-name"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Slug updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to update slug")
     * )
     */
    public function updateSlug(UpdateSlugRequest $request, $addressSlug): JsonResponse
    {
        try {
            $address = $this->addressService->getAddressBySlug($addressSlug);

            $this->authorize('update', $address);

            $updatedAddress = $this->addressService->updateSlug($address, $request->input('new_slug'));

            return $this->sendResponse($updatedAddress, 'Slug updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update slug.', 500, ['error' => $e->getMessage()]);
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

    private function addDefaultRoom(Address $address): Room | JsonResponse
    {
        return $this->roomService->createRoom('Room1', $address);
    }

    public function getRandomStudio(): JsonResponse
    {
        try {
            $studio = $this->addressService->getRandomStudio();
            return $this->sendResponse($studio, 'Random studio retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve random studio.', 500, ['error' => $e->getMessage()]);
        }
    }
}
