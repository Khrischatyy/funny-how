<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Square\SquareClient;
use Square\Models\ObtainTokenRequest;
use Exception;
use App\Models\SquareToken;
use App\Models\SquareLocation;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    public function redirectToSquare()
    {
        $clientId = env('SQUARE_APPLICATION_ID');
        $redirectUri = env('APP_URL') . '/auth/square';
        $scope = 'MERCHANT_PROFILE_READ PAYMENTS_WRITE PAYMENTS_READ PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS ORDERS_WRITE ORDERS_READ';
        $squareappBaseUrl = env('SQUARE_ENVIRONMENT') == 'production' ? 'https://connect.squareup.com' : 'https://connect.squareupsandbox.com';

        $url = "{$squareappBaseUrl}/oauth2/authorize?client_id={$clientId}&scope={$scope}&session=false&redirect_uri={$redirectUri}";

        return response()->json(['url' => $url]);
    }

    public function handleSquareCallback(Request $request)
    {
        $code = $request->input('code');
        $redirectUri = env('APP_URL') . '/auth/square';

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
            $body->setRedirectUri($redirectUri);
            $body->setRedirectUri($redirectUri);

            $apiResponse = $client->getOAuthApi()->obtainToken($body);

            if ($apiResponse->isSuccess()) {
                $result = $apiResponse->getResult();
                $user = Auth::user();

                $user->payment_gateway = 'square';
                $user->save();

                // Сохранение токенов в базу данных
                SquareToken::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'access_token' => $result->getAccessToken(),
                        'refresh_token' => $result->getRefreshToken(),
                        'expires_at' => $result->getExpiresAt(),
                    ]
                );

                return $this->getLocations($user, $result->getAccessToken());
            } else {
                return $this->sendError('Failed to obtain token.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to obtain token.', 500, ['error' => $e->getMessage()]);
        }
    }

    protected function getLocations($user, $accessToken)
    {
        try {
            $client = new SquareClient([
                'accessToken' => $accessToken,
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $apiResponse = $client->getLocationsApi()->listLocations();

            if ($apiResponse->isSuccess()) {
                $locations = $apiResponse->getResult()->getLocations();

                $addressId = $user->company->addresses()->first()->id;

                if (count($locations) > 0) {
                    foreach ($locations as $location) {
                        SquareLocation::updateOrCreate(
                            ['address_id' => $addressId], // предполагаем, что у пользователя есть адрес
                            ['location_id' => $location->getId()]
                        );
                    }

                    return $this->sendResponse($locations, 'Existing locations retrieved successfully.');
                } else {
                    return $this->sendError('No locations found.', 404);
                }
            } else {
                return $this->sendError('Failed to retrieve locations.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve locations.', 500, ['error' => $e->getMessage()]);
        }
    }
}