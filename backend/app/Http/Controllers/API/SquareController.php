<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\Payment\Gateways\SquareService;
use Exception;
use Illuminate\Http\Request;

class SquareController extends BaseController
{
    public function __construct(public SquareService $squareService)
    {}

    public function redirectToSquare()
    {
        try {
            $url = $this->squareService->getSquareRedirectUrl();
            return $this->sendResponse(['url' => $url], 'Redirect URL generated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to generate redirect URL.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function handleSquareCallback(Request $request)
    {
        try {
            $user = $this->squareService->handleSquareCallback($request);
            return $this->sendResponse($user, 'Square installed successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to handle Square callback.', 500, ['error' => $e->getMessage()]);
        }
    }
}