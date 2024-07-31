<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Services\Payment\Gateways\SquareService;
use App\Models\Address as AppAddress;
use Square\SquareClient;
use Square\Models\CreateLocationRequest;
use Square\Models\Address as SquareAddress;
use Square\Models\Location;
use Exception;


class PaymentController extends BaseController
{
    protected $squareClient;
    protected $squareService;

    public function __construct(SquareService $squareService)
    {
        $this->squareService = $squareService;
        $this->squareClient = new SquareClient([
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
        ]);
    }

    /**
     * @OA\Post(
     *     path="/payment/square/create-location",
     *     summary="Create a Square location for an address",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Square location created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Square location created successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function createLocation(Request $request)
    {
        $addressId = $request->input('address_id');

        try {
            $address = AppAddress::findOrFail($addressId);

            $squareAddress = new SquareAddress();
            $squareAddress->setAddressLine1($address->street);
            $squareAddress->setLocality($address->city);
            $squareAddress->setPostalCode($address->postal_code ?? '00000'); // Убедимся, что значение установлено
            $squareAddress->setAdministrativeDistrictLevel1($address->state ?? 'Unknown'); // Убедимся, что значение установлено
            $squareAddress->setCountry('US'); // Установите вашу страну

            $location = new Location();
            $location->setName($address->name ?? 'Default Location Name sdfa'); // Убедимся, что значение установлено
            $location->setAddress($squareAddress);
            $location->setDescription('Description of your location'); // Добавьте описание, если нужно

            $body = new CreateLocationRequest();
            $body->setLocation($location);

            // Создаем клиент Square с явным указанием токена
            $squareClient = new SquareClient([
                'accessToken' => env('SQUARE_ACCESS_TOKEN'),
                'environment' => env('SQUARE_ENVIRONMENT', 'production')
            ]);

            $apiResponse = $squareClient->getLocationsApi()->createLocation($body);

            if ($apiResponse->isSuccess()) {
                $locationId = $apiResponse->getResult()->getLocation()->getId();
                $address->square_location_id = $locationId;
                $address->save();

                return $this->sendResponse(['location_id' => $locationId], 'Square location created successfully.');
            } else {
                return $this->sendError('Failed to create Square location.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to create Square location.', 500, ['error' => $e->getMessage()]);
        }
    }
}