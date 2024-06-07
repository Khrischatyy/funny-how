<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Funny How Documentation API",
 *     version="1.0.0"
 * )
 */

class BaseController extends Controller
{
    public function sendResponse($result, $message, $code = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
            'code' => $code,
        ];

        return response()->json($response, 200);
    }


    /**
     * Return an error response.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError(string $error, int $code = 404, array $errorMessages = []): JsonResponse
    {
        $response = [
            'success' => false,
            'code' => $code,
            'error' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
