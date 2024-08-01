<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Square\SquareClient;
use Square\Models\ObtainTokenRequest;
use Square\Models\CreateLocationRequest;
use Exception;
use App\Models\SquareToken;
use App\Models\SquareLocation;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    public function redirectToSquare()
    {
        $clientId = env('SQUARE_APPLICATION_ID');
        $redirectUri = route('square.callback');
        $scope = 'MERCHANT_PROFILE_READ PAYMENTS_WRITE PAYMENTS_READ';

        $url = "https://connect.squareup.com/oauth2/authorize?client_id={$clientId}&scope={$scope}&session=false&redirect_uri={$redirectUri}";

        return redirect($url);
    }

    public function handleSquareCallback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return $this->sendError('Authorization code not found.', 400);
        }

        try {
            $client = new SquareClient([
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $body = new ObtainTokenRequest(
                env('SQUARE_APPLICATION_ID'),
                'authorization_code'
            );

            $body->setClientSecret(env('SQUARE_CLIENT_SECRET'));
            $body->setCode($code);
            $body->setRedirectUri(route('square.callback'));

            $apiResponse = $client->getOAuthApi()->obtainToken($body);

            if ($apiResponse->isSuccess()) {
                $result = $apiResponse->getResult();
                $user = Auth::user();

                // Сохранение токенов в базу данных
                SquareToken::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'access_token' => $result->getAccessToken(),
                        'refresh_token' => $result->getRefreshToken(),
                        'expires_at' => $result->getExpiresAt(),
                    ]
                );

                // Получение и создание локаций
                return $this->getOrCreateLocations($user, $result->getAccessToken());
            } else {
                return $this->sendError('Failed to obtain token.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to obtain token.', 500, ['error' => $e->getMessage()]);
        }
    }

    protected function getOrCreateLocations($user, $accessToken)
    {
        try {
            $client = new SquareClient([
                'accessToken' => $accessToken,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $apiResponse = $client->getLocationsApi()->listLocations();

            if ($apiResponse->isSuccess()) {
                $locations = $apiResponse->getResult()->getLocations();

                $addressId = $user->addresses()->first()->id;

                if (count($locations) > 0) {
                    foreach ($locations as $location) {
                        SquareLocation::updateOrCreate(
                            ['address_id' => $addressId], // предполагаем, что у пользователя есть адрес
                            ['location_id' => $location->getId()]
                        );
                    }

                    return $this->sendResponse($locations, 'Existing locations retrieved successfully.');
                } else {
                    return $this->createLocationForUser($user, $accessToken);
                }
            } else {
                return $this->sendError('Failed to retrieve locations.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve locations.', 500, ['error' => $e->getMessage()]);
        }
    }

    protected function createLocationForUser($user, $accessToken)
    {
        $address = $user->addresses()->first(); // предполагаем, что у пользователя есть адрес

        try {
            $squareAddress = new \Square\Models\Address();
            $squareAddress->setAddressLine1($address->street);
            $squareAddress->setLocality($address->city);
            $squareAddress->setPostalCode($address->postal_code ?? '00000');
            $squareAddress->setAdministrativeDistrictLevel1($address->state ?? 'Unknown');
            $squareAddress->setCountry('US');

            $location = new \Square\Models\Location();
            $location->setName($address->name ?? 'Default Location Name');
            $location->setAddress($squareAddress);
            $location->setDescription('Description of your location');

            $body = new CreateLocationRequest();
            $body->setLocation($location);

            $squareClient = new SquareClient([
                'accessToken' => $accessToken,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $apiResponse = $squareClient->getLocationsApi()->createLocation($body);

            if ($apiResponse->isSuccess()) {
                $locationId = $apiResponse->getResult()->getLocation()->getId();

                SquareLocation::create([
                    'address_id' => $address->id,
                    'location_id' => $locationId
                ]);

                return $this->sendResponse(['location_id' => $locationId], 'Square location created successfully.');
            } else {
                return $this->sendError('Failed to create Square location.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to create Square location.', 500, ['error' => $e->getMessage()]);
        }
    }
}