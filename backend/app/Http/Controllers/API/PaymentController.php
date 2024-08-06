<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\Payment\Gateways\SquareService;
use Illuminate\Http\JsonResponse;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    public function __construct(public PaymentService $paymentService, public SquareService $squareService)
    {}

    public function retrieveAccount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cards = $this->paymentService->retrieveAccount($user);
            return $this->sendResponse($cards, 'Account info retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve cards.', 500, ['error' => $e->getMessage()]);
        }
    }
}