<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Models\SquareToken;
use Exception;
use Illuminate\Http\Request;
use Square\Models\ObtainTokenRequest;
use Square\SquareClient;

class PaymentController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/payment/square/token",
     *     summary="Obtain or refresh Square token",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="authorization_code_or_refresh_token"),
     *             @OA\Property(property="is_refresh", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token obtained or refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="refresh_token", type="string"),
     *             @OA\Property(property="expires_at", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function obtainToken(Request $request)
    {
        $code = $request->input('code');
        $isRefresh = $request->input('is_refresh', false);
        $userId = $request->user()->id; // или другой способ получения идентификатора пользователя

        try {
            $client = new SquareClient([
                'accessToken' => env('SQUARE_ACCESS_TOKEN'),
                'environment' => env('SQUARE_ENVIRONMENT', 'sandbox')
            ]);

            $body = new ObtainTokenRequest(
                env('SQUARE_APPLICATION_ID'),
                $isRefresh ? 'refresh_token' : 'authorization_code'
            );

            $body->setClientSecret(env('SQUARE_CLIENT_SECRET'));
            $body->setCode($code);

            if (!$isRefresh) {
                $body->setRedirectUri(env('APP_URL') . '/auth/square');
            }

            $apiResponse = $client->getOAuthApi()->obtainToken($body);

            if ($apiResponse->isSuccess()) {
                $result = $apiResponse->getResult();

                // Сохранение токенов в базу данных
                SquareToken::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'access_token' => $result->getAccessToken(),
                        'refresh_token' => $result->getRefreshToken(),
                        'expires_at' => $result->getExpiresAt(),
                    ]
                );

                return $this->sendResponse([
                    'access_token' => $result->getAccessToken(),
                    'refresh_token' => $result->getRefreshToken(),
                    'expires_at' => $result->getExpiresAt(),
                ], 'Token obtained or refreshed successfully.');
            } else {
                return $this->sendError('Failed to obtain token.', 400, ['errors' => $apiResponse->getErrors()]);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to obtain token.', 500, ['error' => $e->getMessage()]);
        }
    }
}